<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\UpdateCallOffLetterRequest;
use App\Models\CallOff;
use App\Models\ProvinceDistribution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CallOffLetterController extends Controller
{
    private const DEFAULT_NEFA_TITLE =
        'Supply and Delivery of Personal Protective Equipment '
        . 'for the implementation of TUPAD Program under '
        . 'Framework Agreement';

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->query('search', '')
        );

        $status = trim(
            (string) $request->query('status', '')
        );

        $callOffs = CallOff::query()
            ->with([
                'distributionBatch.purchaseOrder.supplier',
            ])
            ->when(
                $status !== '',
                fn (Builder $query) =>
                    $query->where('status', $status)
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->where(
                                    'call_off_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'nefa_title',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder',
                                    function (
                                        Builder $purchaseOrderQuery
                                    ) use ($search): void {
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
                                    'distributionBatch.purchaseOrder.supplier',
                                    fn (Builder $supplierQuery) =>
                                        $supplierQuery->where(
                                            'supplier_name',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                );
                        }
                    );
                }
            )
            ->orderByDesc('call_off_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'tssd.call-off-letters.index',
            [
                'callOffs' => $callOffs,
                'search' => $search,
                'status' => $status,
                'defaultNefaTitle' => self::DEFAULT_NEFA_TITLE,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(CallOff $callOff): View
    {
        $callOff->load([
            'distributionBatch.purchaseOrder.supplier',
        ]);

        return view(
            'tssd.call-off-letters.edit',
            [
                'callOff' => $callOff,
                'defaultNefaTitle' => self::DEFAULT_NEFA_TITLE,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update editable NEFA title
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateCallOffLetterRequest $request,
        CallOff $callOff
    ): RedirectResponse {
        $callOff->update([
            'nefa_title' => trim(
                (string) $request->validated('nefa_title')
            ),
        ]);

        return redirect()
            ->route(
                'tssd.call-off-letters.edit',
                $callOff
            )
            ->with(
                'success',
                'The NEFA project title was updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Print preview
    |--------------------------------------------------------------------------
    */

    public function print(CallOff $callOff): View
    {
        $callOff->load([
            'distributionBatch.purchaseOrder.supplier',
        ]);

        $batch = $callOff->distributionBatch;

        abort_unless(
            $batch,
            404,
            'The selected Call-Off has no distribution batch.'
        );

        $distributions = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
            ])
            ->where(
                'tssd_distribution_batch_id',
                $batch->id
            )
            ->orderBy('province_id')
            ->get();

        $rows = $distributions
            ->map(
                fn (ProvinceDistribution $distribution): array =>
                    $this->makeDistributionRow($distribution)
            );

        $totals = [
            'long_sleeve_medium' =>
                (int) $rows->sum('long_sleeve_medium'),

            'long_sleeve_large' =>
                (int) $rows->sum('long_sleeve_large'),

            'bucket_hat' =>
                (int) $rows->sum('bucket_hat'),

            'rubber_boots_us9' =>
                (int) $rows->sum('rubber_boots_us9'),

            'rubber_boots_us10' =>
                (int) $rows->sum('rubber_boots_us10'),

            'hand_gloves' =>
                (int) $rows->sum('hand_gloves'),
        ];

        return view(
            'tssd.call-off-letters.print',
            [
                'callOff' => $callOff,
                'batch' => $batch,
                'purchaseOrder' => $batch->purchaseOrder,
                'rows' => $rows,
                'totals' => $totals,
                'nefaTitle' => $callOff->nefa_title
                    ?: self::DEFAULT_NEFA_TITLE,
                'callOffLabel' => $this->makeCallOffLabel(
                    $callOff->call_off_number
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create one printable province row
    |--------------------------------------------------------------------------
    */

    private function makeDistributionRow(
        ProvinceDistribution $distribution
    ): array {
        return [
            'province' =>
                $distribution->province?->name ?? '—',

            'place_of_delivery' =>
                $distribution->place_of_delivery
                ?: $distribution->province?->delivery_address
                ?: '—',

            'delivery_date' =>
                $distribution->scheduled_delivery_date,

            'long_sleeve_medium' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'long sleeve medium',
                        'long sleeves medium',
                        'long sleeve m',
                        'long sleeves m',
                    ],
                    'medium'
                ),

            'long_sleeve_large' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'long sleeve large',
                        'long sleeves large',
                        'long sleeve l',
                        'long sleeves l',
                    ],
                    'large'
                ),

            'bucket_hat' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'bucket hat',
                        'bucket hats',
                    ]
                ),

            'rubber_boots_us9' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'rubber boots us9',
                        'rubber boot us9',
                        'rubber boots uk9',
                        'rubber boot uk9',
                        'rubber boots 9',
                    ],
                    '9'
                ),

            'rubber_boots_us10' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'rubber boots us10',
                        'rubber boot us10',
                        'rubber boots uk10',
                        'rubber boot uk10',
                        'rubber boots 10',
                    ],
                    '10'
                ),

            'hand_gloves' =>
                $this->findItemQuantity(
                    $distribution->items,
                    [
                        'hand gloves',
                        'hand glove',
                        'gloves',
                        'glove',
                    ]
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Find item quantity using item name and size
    |--------------------------------------------------------------------------
    */

    private function findItemQuantity(
        Collection $distributionItems,
        array $possibleNames,
        ?string $requiredSize = null
    ): int {
        $possibleNames = collect($possibleNames)
            ->map(
                fn (string $name): string =>
                    $this->normalize($name)
            );

        $matchingItems = $distributionItems->filter(
            function ($distributionItem) use (
                $possibleNames,
                $requiredSize
            ): bool {
                $item = $distributionItem->item;

                if (! $item) {
                    return false;
                }

                $name = $this->normalize(
                    (string) $item->name
                );

                $category = $this->normalize(
                    (string) (
                        $item->category
                        ?? $item->type
                        ?? ''
                    )
                );

                $size = $this->normalize(
                    (string) ($item->size ?? '')
                );

                $combined = trim(
                    $name . ' ' . $category . ' ' . $size
                );

                $nameMatched = $possibleNames->contains(
                    fn (string $possibleName): bool =>
                        Str::contains(
                            $combined,
                            $possibleName
                        )
                );

                if (! $nameMatched) {
                    return false;
                }

                if ($requiredSize === null) {
                    return true;
                }

                return Str::contains(
                    $combined,
                    $this->normalize($requiredSize)
                );
            }
        );

        return (int) $matchingItems->sum('quantity');
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replace(['-', '_', '/', '(', ')'], ' ')
            ->squish()
            ->toString();
    }

    /*
    |--------------------------------------------------------------------------
    | Convert a Call-Off value into a readable label
    |--------------------------------------------------------------------------
    */

    private function makeCallOffLabel(
        ?string $callOffNumber
    ): string {
        $number = $this->extractSequenceNumber(
            (string) $callOffNumber
        );

        if ($number === null) {
            return $callOffNumber
                ? 'Call-Off ' . $callOffNumber
                : 'Call-Off';
        }

        return $this->ordinal($number) . ' Call-Off';
    }

    private function extractSequenceNumber(
        string $value
    ): ?int {
        if (
            preg_match(
                '/(?:^|\D)(\d{1,2})(?:st|nd|rd|th)?(?:\D|$)/i',
                $value,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        return null;
    }

    private function ordinal(int $number): string
    {
        $lastTwoDigits = $number % 100;

        if (
            $lastTwoDigits >= 11
            && $lastTwoDigits <= 13
        ) {
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