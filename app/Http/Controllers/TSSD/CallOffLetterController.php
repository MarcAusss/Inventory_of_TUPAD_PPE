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
use Illuminate\View\View;

class CallOffLetterController extends Controller
{
    private const DEFAULT_NEFA_TITLE =
        'Supply and Delivery of Personal Protective Equipment '
        . 'for the implementation of TUPAD Program under '
        . 'Framework Agreement';

    /*
    |--------------------------------------------------------------------------
    | PPE Item IDs
    |--------------------------------------------------------------------------
    |
    | These IDs are based on the records shown in your debugging result.
    |
    */

    private const LONG_SLEEVE_MEDIUM_ID = 1;
    private const LONG_SLEEVE_LARGE_ID = 2;
    private const BUCKET_HAT_ID = 3;
    private const RUBBER_BOOTS_US9_ID = 4;
    private const RUBBER_BOOTS_US10_ID = 5;
    private const HAND_GLOVES_ID = 6;
    private const FACE_MASK_ID = 7;

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
    | Update
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
                'items',
            ])
            ->where(
                'tssd_distribution_batch_id',
                $batch->id
            )
            ->orderBy('province_id')
            ->get();

        $rows = $distributions->map(
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

            'face_mask' =>
                (int) $rows->sum('face_mask'),
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
    | Build printable province row
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
                ?: $distribution->province?->office_name
                ?: '—',

            'delivery_date' =>
                $distribution->scheduled_delivery_date,

            'long_sleeve_medium' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::LONG_SLEEVE_MEDIUM_ID
                ),

            'long_sleeve_large' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::LONG_SLEEVE_LARGE_ID
                ),

            'bucket_hat' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::BUCKET_HAT_ID
                ),

            'rubber_boots_us9' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::RUBBER_BOOTS_US9_ID
                ),

            'rubber_boots_us10' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::RUBBER_BOOTS_US10_ID
                ),

            'hand_gloves' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::HAND_GLOVES_ID
                ),

            'face_mask' =>
                $this->quantityByItemId(
                    $distribution->items,
                    self::FACE_MASK_ID
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Find quantity directly from item ID
    |--------------------------------------------------------------------------
    */

    private function quantityByItemId(
        Collection $distributionItems,
        int $itemId
    ): int {
        return (int) $distributionItems
            ->where('item_id', $itemId)
            ->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | Call-Off label
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
        /*
        |--------------------------------------------------------------------------
        | Prefer the final Call-Off sequence
        |--------------------------------------------------------------------------
        |
        | Example:
        | CO-2026-004 becomes 4th Call-Off, not 20th Call-Off.
        |
        */

        if (
            preg_match(
                '/(\d+)\D*$/',
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