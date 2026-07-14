<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use Illuminate\Support\Collection;

class ProvincialReceivingReportService
{
    /**
     * Fixed keys used by the Current Inventory and print tables.
     *
     * @var array<int, string>
     */
    private const ITEM_KEYS = [
        1 => 'long_sleeve_medium',
        2 => 'long_sleeve_large',
        3 => 'bucket_hat',
        4 => 'rubber_boots_us9',
        5 => 'rubber_boots_us10',
        6 => 'hand_gloves',
        7 => 'mask',
    ];

    /**
     * Build one row per Delivery Receipt.
     *
     * Beginning = remaining allocation before the receipt
     * Actual    = quantity received in the receipt
     * Ending    = Beginning - Actual
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rowsForProvince(
        int $provinceId
    ): Collection {
        $allocations = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',

                'deliveryReceipts' => function ($query): void {
                    $query
                        ->where('status', 'Received')
                        ->orderBy('delivery_date')
                        ->orderBy('id');
                },

                'deliveryReceipts.items.item',
                'deliveryReceipts.receivedByUser',
            ])
            ->where('province_id', $provinceId)

            /*
             * Provincial Office must not see pending or rejected
             * Call-Off records.
             */
            ->whereHas(
                'distributionBatch.callOff',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        'Approved',
                        'Completed',
                    ]
                )
            )
            ->whereIn(
                'status',
                [
                    'Approved',
                    'For Delivery',
                    'Partially Received',
                    'Received',
                ]
            )
            ->orderBy('id')
            ->get();

        return $allocations
            ->flatMap(
                fn (
                    ProvinceDistribution $allocation
                ): Collection => $this->rowsForAllocation(
                    $allocation
                )
            )
            ->values();
    }

    /**
     * Build receiving rows for one Call-Off provincial allocation.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rowsForAllocation(
        ProvinceDistribution $allocation
    ): Collection {
        $allocation->loadMissing([
            'province',
            'items.item',
            'distributionBatch.callOff',
            'distributionBatch.purchaseOrder.supplier',
            'deliveryReceipts.items.item',
            'deliveryReceipts.receivedByUser',
        ]);

        $runningBalance = $this->allocationQuantities(
            $allocation
        );

        $receipts = $allocation
            ->deliveryReceipts
            ->where('status', 'Received')
            ->sortBy(
                fn (
                    DeliveryReceipt $receipt
                ): string => (
                    $receipt->delivery_date
                        ?->format('Y-m-d')
                    ?? '0000-00-00'
                )
                    .'|'
                    .str_pad(
                        (string) $receipt->id,
                        20,
                        '0',
                        STR_PAD_LEFT
                    )
            )
            ->values();

        $rows = collect();

        foreach ($receipts as $receipt) {
            $beginning = $runningBalance;

            $actual = $this->receiptQuantities(
                $receipt
            );

            $ending = [];

            foreach (
                array_keys(self::ITEM_KEYS)
                as $itemId
            ) {
                $beginningQuantity = (int) (
                    $beginning[$itemId]
                    ?? 0
                );

                $actualQuantity = (int) (
                    $actual[$itemId]
                    ?? 0
                );

                $ending[$itemId] = max(
                    0,
                    $beginningQuantity
                        - $actualQuantity
                );
            }

            $rows->push(
                $this->makeRow(
                    allocation: $allocation,
                    receipt: $receipt,
                    beginning: $beginning,
                    actual: $actual,
                    ending: $ending
                )
            );

            /*
             * The remaining undelivered quantity becomes the next
             * Delivery Receipt's beginning balance.
             */
            $runningBalance = $ending;
        }

        return $rows->values();
    }

    /**
     * Build one table row.
     *
     * @param array<int, int> $beginning
     * @param array<int, int> $actual
     * @param array<int, int> $ending
     *
     * @return array<string, mixed>
     */
    private function makeRow(
        ProvinceDistribution $allocation,
        DeliveryReceipt $receipt,
        array $beginning,
        array $actual,
        array $ending
    ): array {
        $batch = $allocation
            ->distributionBatch;

        $callOff = $batch
            ?->callOff;

        $purchaseOrder = $batch
            ?->purchaseOrder;

        $supplier = $purchaseOrder
            ?->supplier;

        return [
            'province_distribution_id' =>
                (int) $allocation->id,

            'delivery_receipt_id' =>
                (int) $receipt->id,

            'call_off_number' =>
                $callOff?->call_off_number
                ?? '—',

            'purchase_order_number' =>
                $purchaseOrder?->po_number
                ?? '—',

            'supplier_id' =>
                $supplier?->id,

            'supplier_name' =>
                $supplier?->supplier_name
                ?? '—',

            'delivery_receipt_number' =>
                $receipt->dr_number
                ?? '—',

            'delivery_date' =>
                $receipt->delivery_date,

            'receiver_name' =>
                $receipt->physical_receiver_name
                ?? $receipt->receivedByUser?->name
                ?? $receipt->received_by
                ?? '—',

            'document' =>
                $receipt->document,

            'status' =>
                $allocation->status,

            'beginning' =>
                $this->toNamedBreakdown(
                    $beginning
                ),

            'actual' =>
                $this->toNamedBreakdown(
                    $actual
                ),

            'ending' =>
                $this->toNamedBreakdown(
                    $ending
                ),

            'beginning_total' =>
                array_sum($beginning),

            'actual_total' =>
                array_sum($actual),

            'ending_total' =>
                array_sum($ending),

            'allocation' =>
                $allocation,

            'receipt' =>
                $receipt,
        ];
    }

    /**
     * Original TSSD allocation by item ID.
     *
     * @return array<int, int>
     */
    private function allocationQuantities(
        ProvinceDistribution $allocation
    ): array {
        $quantities = array_fill_keys(
            array_keys(self::ITEM_KEYS),
            0
        );

        foreach ($allocation->items as $item) {
            $itemId = (int) $item->item_id;

            if (! array_key_exists(
                $itemId,
                self::ITEM_KEYS
            )) {
                continue;
            }

            $quantities[$itemId] +=
                (int) $item->quantity;
        }

        return $quantities;
    }

    /**
     * Actual quantities received in one Delivery Receipt.
     *
     * @return array<int, int>
     */
    private function receiptQuantities(
        DeliveryReceipt $receipt
    ): array {
        $quantities = array_fill_keys(
            array_keys(self::ITEM_KEYS),
            0
        );

        foreach ($receipt->items as $item) {
            $itemId = (int) $item->item_id;

            if (! array_key_exists(
                $itemId,
                self::ITEM_KEYS
            )) {
                continue;
            }

            $quantities[$itemId] += (int) (
                $item->received_quantity
                ?? $item->quantity
                ?? 0
            );
        }

        return $quantities;
    }

    /**
     * Convert item IDs to the keys expected by the Blade.
     *
     * @param array<int, int> $quantities
     *
     * @return array<string, int>
     */
    private function toNamedBreakdown(
        array $quantities
    ): array {
        $breakdown = [];

        foreach (
            self::ITEM_KEYS
            as $itemId => $key
        ) {
            $breakdown[$key] = (int) (
                $quantities[$itemId]
                ?? 0
            );
        }

        return $breakdown;
    }
}