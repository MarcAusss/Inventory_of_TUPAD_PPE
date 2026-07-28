<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\UpdateCallOffLetterRequest;
use App\Models\Item;
use App\Models\ProvinceDistribution;
use App\Models\TssdDistributionBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CallOffLetterController extends Controller
{
    private const DEFAULT_NEFA_TITLE =
        'Supply and Delivery of Personal Protective Equipment '
        . 'for the implementation of TUPAD Program under '
        . 'Framework Agreement';


    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $letterBatches = TssdDistributionBatch::query()
            ->with([
                'purchaseOrder.supplier',
                'callOff.assignedBy',
                'callOff.approvedBy',
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('call_off_letter_submitted_at')
                    ->orWhereHas('callOff');
            })
            ->when(
                $status === 'Pending Call-Off Number',
                fn (Builder $query) => $query
                    ->where('status', 'Submitted')
                    ->doesntHave('callOff')
            )
            ->when(
                $status === 'Approved',
                fn (Builder $query) => $query->whereHas(
                    'callOff',
                    fn (Builder $callOffQuery) =>
                        $callOffQuery->where('status', 'Approved')
                )
            )
            ->when(
                $status === 'Rejected',
                fn (Builder $query) => $query->whereHas(
                    'callOff',
                    fn (Builder $callOffQuery) =>
                        $callOffQuery->where('status', 'Rejected')
                )
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(function (Builder $subQuery) use ($search): void {
                        $subQuery
                            ->where(
                                'call_off_letter_nefa_title',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhereHas(
                                'callOff',
                                fn (Builder $callOffQuery) =>
                                    $callOffQuery->where(
                                        'call_off_number',
                                        'like',
                                        '%' . $search . '%'
                                    )
                            )
                            ->orWhereHas(
                                'purchaseOrder',
                                function (Builder $purchaseOrderQuery) use ($search): void {
                                    $purchaseOrderQuery
                                        ->where(
                                            'po_number',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                        ->orWhere(
                                            'nefa_number',
                                            'like',
                                            '%' . $search . '%'
                                        );
                                }
                            )
                            ->orWhereHas(
                                'purchaseOrder.supplier',
                                fn (Builder $supplierQuery) =>
                                    $supplierQuery->where(
                                        'supplier_name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                            );
                    });
                }
            )
            ->latest('call_off_letter_submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('tssd.call-off-letters.index', [
            'letterBatches' => $letterBatches,
            'search' => $search,
            'status' => $status,
            'defaultNefaTitle' => self::DEFAULT_NEFA_TITLE,
        ]);
    }

    public function edit(
        TssdDistributionBatch $distributionBatch
    ): View {
        $this->ensureLetterExists($distributionBatch);

        $distributionBatch->load([
            'purchaseOrder.supplier',
            'callOff',
        ]);

        return view('tssd.call-off-letters.edit', [
            'distributionBatch' => $distributionBatch,
            'defaultNefaTitle' => self::DEFAULT_NEFA_TITLE,
        ]);
    }

    public function update(
        UpdateCallOffLetterRequest $request,
        TssdDistributionBatch $distributionBatch
    ): RedirectResponse {
        $this->ensureLetterExists($distributionBatch);

        $values = [
            'call_off_letter_nefa_title' => trim(
                (string) $request->validated('nefa_title')
            ),
            'call_off_letter_total_amount' =>
                $request->validated('print_total_amount'),
            'call_off_letter_margin_top' =>
                $request->validated('print_margin_top'),
            'call_off_letter_margin_right' =>
                $request->validated('print_margin_right'),
            'call_off_letter_margin_bottom' =>
                $request->validated('print_margin_bottom'),
            'call_off_letter_margin_left' =>
                $request->validated('print_margin_left'),
        ];

        $distributionBatch->update($values);

        if ($distributionBatch->callOff) {
            $distributionBatch->callOff->update([
                'nefa_title' => $values['call_off_letter_nefa_title'],
                'print_total_amount' => $values['call_off_letter_total_amount'],
                'print_margin_top' => $values['call_off_letter_margin_top'],
                'print_margin_right' => $values['call_off_letter_margin_right'],
                'print_margin_bottom' => $values['call_off_letter_margin_bottom'],
                'print_margin_left' => $values['call_off_letter_margin_left'],
            ]);
        }

        return redirect()
            ->route('tssd.call-off-letters.edit', $distributionBatch)
            ->with(
                'success',
                'The Call-Off request letter settings were updated successfully.'
            );
    }

    public function print(
        TssdDistributionBatch $distributionBatch
    ): View {
        $user = request()->user();

        abort_unless(
            $user && ($user->isTssd() || $user->isSupply()),
            403
        );

        $this->ensureLetterExists($distributionBatch);

        $distributionBatch->load([
            'purchaseOrder.supplier',
            'callOff',
        ]);

        $distributions = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
            ])
            ->where(
                'tssd_distribution_batch_id',
                $distributionBatch->id
            )
            ->orderBy('province_id')
            ->get();

        $itemColumns = $distributions
            ->flatMap(fn (ProvinceDistribution $distribution) => $distribution->items)
            ->pluck('item')
            ->filter()
            ->unique('id')
            ->sortBy(fn (Item $item): string => strtolower(
                $item->item_name . '|' . ($item->label ?? '')
            ))
            ->values();

        $rows = $distributions->map(
            fn (ProvinceDistribution $distribution): array =>
                $this->makeDistributionRow($distribution)
        );

        $totals = $this->makeTotals($rows, $itemColumns);
        $callOff = $distributionBatch->callOff;
        $purchaseOrder = $distributionBatch->purchaseOrder;

        $backUrl = request()->routeIs('supply.*')
            ? route('supply.call-offs.show', $distributionBatch)
            : route('tssd.call-off-letters.edit', $distributionBatch);

        return view('tssd.call-off-letters.print', [
            'callOff' => $callOff,
            'batch' => $distributionBatch,
            'purchaseOrder' => $purchaseOrder,
            'rows' => $rows,
            'totals' => $totals,
            'itemColumns' => $itemColumns,
            'nefaTitle' =>
                $distributionBatch->call_off_letter_nefa_title
                ?: $callOff?->nefa_title
                ?: self::DEFAULT_NEFA_TITLE,
            'callOffLabel' => $callOff
                ? $this->makeCallOffLabel($callOff->call_off_number)
                : 'assignment of an official Call-Off Number for the distribution',
            'printDistributionBatch' =>
                $this->ordinal((int) $distributionBatch->id),
            'printTotalAmount' =>
                $distributionBatch->call_off_letter_total_amount
                ?? $callOff?->print_total_amount
                ?? $purchaseOrder?->total_amount
                ?? 0,
            'printMargins' => [
                'top' => (float) (
                    $distributionBatch->call_off_letter_margin_top
                    ?? $callOff?->print_margin_top
                    ?? 9
                ),
                'right' => (float) (
                    $distributionBatch->call_off_letter_margin_right
                    ?? $callOff?->print_margin_right
                    ?? 11
                ),
                'bottom' => (float) (
                    $distributionBatch->call_off_letter_margin_bottom
                    ?? $callOff?->print_margin_bottom
                    ?? 28
                ),
                'left' => (float) (
                    $distributionBatch->call_off_letter_margin_left
                    ?? $callOff?->print_margin_left
                    ?? 11
                ),
            ],
            'documentDate' =>
                $callOff?->call_off_date
                ?? $distributionBatch->call_off_letter_submitted_at
                ?? $distributionBatch->distribution_date
                ?? now(),
            'isDraftPreview' => false,
            'backUrl' => $backUrl,
        ]);
    }

    private function ensureLetterExists(
        TssdDistributionBatch $distributionBatch
    ): void {
        abort_unless(
            $distributionBatch->call_off_letter_submitted_at
                || $distributionBatch->callOff()->exists(),
            404,
            'This distribution has no generated Call-Off request letter.'
        );
    }

    private function makeDistributionRow(
        ProvinceDistribution $distribution
    ): array {
        return [
            'province' => $distribution->province?->name ?? '—',
            'place_of_delivery' =>
                $distribution->place_of_delivery
                ?: $distribution->province?->delivery_address
                ?: $distribution->province?->office_name
                ?: '—',
            'delivery_date' => $distribution->scheduled_delivery_date,
            'items' => $distribution->items
                ->mapWithKeys(fn ($distributionItem): array => [
                    (int) $distributionItem->item_id => (int) $distributionItem->quantity,
                ])
                ->all(),
        ];
    }

    private function makeTotals(Collection $rows, Collection $itemColumns): array
    {
        return $itemColumns
            ->mapWithKeys(fn (Item $item): array => [
                $item->id => (int) $rows->sum(
                    fn (array $row): int => (int) ($row['items'][$item->id] ?? 0)
                ),
            ])
            ->all();
    }

    private function makeCallOffLabel(?string $callOffNumber): string
    {
        $number = $this->extractSequenceNumber((string) $callOffNumber);

        if ($number === null) {
            return $callOffNumber
                ? 'Call-Off ' . $callOffNumber
                : 'Call-Off';
        }

        return $this->ordinal($number) . ' Call-Off';
    }

    private function extractSequenceNumber(string $value): ?int
    {
        if (preg_match('/(\d+)\D*$/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function ordinal(int $number): string
    {
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
            return $number . 'th';
        }

        return match ($number % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }
}
