<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProvinceDistribution;
use App\Models\SupplyDesignation;
use Illuminate\Support\Collection;

class InventoryMovementReportService
{
    /**
     * Build Inventory Movement History for one province.
     *
     * Completed project rows use the historical Call-Off
     * balance snapshots stored in inventory_movements.
     *
     * Beginning Inventory = call_off_balance_before
     * Actual Inventory    = quantity distributed
     * Ending Inventory    = call_off_balance_after
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildForProvince(
        int $provinceId
    ): Collection {
        $allocations = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'deliveryReceipts.items.item',
                'supplyDesignations.items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereHas(
                'distributionBatch.callOff'
            )
            ->orderBy('id')
            ->get();

        return $allocations
            ->flatMap(
                fn (
                    ProvinceDistribution $allocation
                ): Collection => $this->buildAllocationRows(
                    $allocation
                )
            )
            ->values();
    }

    /**
     * Build report rows for one Call-Off allocation.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildAllocationRows(
        ProvinceDistribution $allocation
    ): Collection {
        $allocation->loadMissing([
            'province',
            'items.item',
            'distributionBatch.callOff',
            'distributionBatch.purchaseOrder.supplier',
            'deliveryReceipts.items.item',
            'supplyDesignations.items.item',
        ]);

        $designations = $allocation
            ->supplyDesignations
            ->where(
                'status',
                'Completed'
            )
            ->sortBy(
                fn (
                    SupplyDesignation $designation
                ): string => (
                    $designation
                        ->designation_date
                        ?->format('Y-m-d')
                    ?? '0000-00-00'
                )
                .'|'
                .str_pad(
                    (string) $designation->id,
                    20,
                    '0',
                    STR_PAD_LEFT
                )
            )
            ->values();

        /*
         * No project distribution yet.
         *
         * Show the current opening Call-Off inventory.
         */
        if ($designations->isEmpty()) {
            return collect([
                $this->makeOpeningRow(
                    $allocation
                ),
            ]);
        }

        return $designations
            ->map(
                fn (
                    SupplyDesignation $designation
                ): array => $this->makeDesignationRow(
                    $allocation,
                    $designation
                )
            )
            ->values();
    }

    /**
     * Build one historical project movement row.
     *
     * This method DOES NOT recalculate historical balances.
     *
     * It reads the snapshots already stored in:
     *
     * call_off_balance_before
     * quantity
     * call_off_balance_after
     *
     * @return array<string, mixed>
     */
    private function makeDesignationRow(
        ProvinceDistribution $allocation,
        SupplyDesignation $designation
    ): array {
        $movements = InventoryMovement::query()
            ->with('item')
            ->where(
                'province_id',
                $allocation->province_id
            )
            ->where(
                'province_distribution_id',
                $allocation->id
            )
            ->where(
                'supply_designation_id',
                $designation->id
            )
            ->where(
                'movement_type',
                'OUT'
            )
            ->orderBy('item_id')
            ->get();

        $beginning = [];

        $actual = [];

        $ending = [];

        /*
         * Build balances directly from movement snapshots.
         */
        foreach ($movements as $movement) {
            $itemId = (int) $movement->item_id;

            $beginning[$itemId] = (int) (
                $movement->call_off_balance_before
                ?? 0
            );

            $actual[$itemId] = (int) $movement->quantity;

            $ending[$itemId] = (int) (
                $movement->call_off_balance_after
                ?? 0
            );
        }

        /*
         * Compatibility fallback.
         *
         * This only applies to historical designation items
         * that do not yet have an InventoryMovement row.
         */
        foreach ($designation->items as $designationItem) {
            $itemId = (int) $designationItem->item_id;

            if (isset($actual[$itemId])) {
                continue;
            }

            $quantity = (int) $designationItem->quantity;

            $actual[$itemId] = $quantity;

            $beginning[$itemId] = 0;

            $ending[$itemId] = 0;
        }

        return $this->baseRow(
            allocation: $allocation,
            designation: $designation,
            beginning: $beginning,
            actual: $actual,
            ending: $ending
        );
    }

    /**
     * Build opening Call-Off row.
     *
     * When no project has consumed PPE yet:
     *
     * Beginning = Actual Received
     * Actual    = 0
     * Ending    = Actual Received
     *
     * @return array<string, mixed>
     */
    private function makeOpeningRow(
        ProvinceDistribution $allocation
    ): array {
        $received = $this->receivedQuantities(
            $allocation
        );

        $actual = [];

        foreach (
            $this->itemIds(
                $allocation,
                $received
            ) as $itemId
        ) {
            $actual[$itemId] = 0;
        }

        return $this->baseRow(
            allocation: $allocation,
            designation: null,
            beginning: $received,
            actual: $actual,
            ending: $received
        );
    }

    /**
     * Build the shared report row structure.
     *
     * @param array<int, int> $beginning
     * @param array<int, int> $actual
     * @param array<int, int> $ending
     *
     * @return array<string, mixed>
     */
    private function baseRow(
        ProvinceDistribution $allocation,
        ?SupplyDesignation $designation,
        array $beginning,
        array $actual,
        array $ending
    ): array {
        $callOff = $allocation
            ->distributionBatch
            ?->callOff;

        $supplier = $allocation
            ->distributionBatch
            ?->purchaseOrder
            ?->supplier;

        $deliveryReceipts = $allocation
            ->deliveryReceipts
            ->sortBy(
                fn ($receipt): string => (
                    $receipt
                        ->delivery_date
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

        return [
            'province_distribution_id' => $allocation->id,

            'call_off_number' =>
                $callOff?->call_off_number
                ?? '—',

            'supplier_name' =>
                $supplier?->supplier_name
                ?? '—',

            /*
             * Delivery Receipt audit information.
             */
            'delivery_receipts' =>
                $deliveryReceipts,

            'delivery_receipt_numbers' =>
                $deliveryReceipts
                    ->pluck('dr_number')
                    ->filter()
                    ->implode(', '),

            'first_delivery_date' =>
                $deliveryReceipts
                    ->min('delivery_date'),

            'last_delivery_date' =>
                $deliveryReceipts
                    ->max('delivery_date'),

            /*
             * Project information.
             */
            'supply_designation_id' =>
                $designation?->id,

            'designation_number' =>
                $designation?->designation_number,

            'project_code' =>
                $designation?->project_code
                ?? '—',

            'project_title' =>
                $designation?->project_title
                ?? $designation?->project_name
                ?? 'No Project Distribution Yet',

            'location' =>
                $designation?->location
                ?? '—',

            'number_of_beneficiaries' =>
                (int) (
                    $designation
                        ?->number_of_beneficiaries
                    ?? 0
                ),

            'number_of_days' =>
                (int) (
                    $designation
                        ?->number_of_days
                    ?? 0
                ),

            /*
             * Project date for completed project rows.
             *
             * Last delivery date is used for an opening row.
             */
            'movement_date' =>
                $designation?->designation_date
                ?? $deliveryReceipts
                    ->max('delivery_date'),

            /*
             * Inventory Movement History values.
             */
            'beginning' => $beginning,

            'actual' => $actual,

            'ending' => $ending,

            /*
             * Summary totals.
             */
            'beginning_total' =>
                array_sum($beginning),

            'actual_total' =>
                array_sum($actual),

            'ending_total' =>
                array_sum($ending),

            /*
             * Source models.
             */
            'designation' => $designation,

            'allocation' => $allocation,
        ];
    }

    /**
     * Sum all actual Delivery Receipt quantities
     * under one Call-Off provincial allocation.
     *
     * Multiple DRs are combined.
     *
     * @return array<int, int>
     */
    private function receivedQuantities(
        ProvinceDistribution $allocation
    ): array {
        $quantities = [];

        foreach (
            $allocation->deliveryReceipts as $receipt
        ) {
            if (
                $receipt->status !== 'Received'
            ) {
                continue;
            }

            foreach (
                $receipt->items as $receiptItem
            ) {
                $itemId = (int) $receiptItem->item_id;

                $quantity = (int) (
                    $receiptItem->received_quantity
                    ?? $receiptItem->quantity
                    ?? 0
                );

                if ($quantity <= 0) {
                    continue;
                }

                $quantities[$itemId] = (
                    $quantities[$itemId]
                    ?? 0
                ) + $quantity;
            }
        }

        return $quantities;
    }

    /**
     * Get all PPE item IDs represented by an allocation.
     *
     * @param array<int, int> $quantities
     *
     * @return Collection<int, int>
     */
    private function itemIds(
        ProvinceDistribution $allocation,
        array $quantities
    ): Collection {
        return collect(
            array_merge(
                $allocation
                    ->items
                    ->pluck('item_id')
                    ->map(
                        fn ($itemId): int =>
                            (int) $itemId
                    )
                    ->all(),

                array_keys(
                    $quantities
                )
            )
        )
            ->unique()
            ->sort()
            ->values();
    }
}