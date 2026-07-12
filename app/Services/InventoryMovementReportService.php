<?php

namespace App\Services;

use App\Models\ProvinceDistribution;
use Illuminate\Support\Collection;

class InventoryMovementReportService
{
    /**
     * Build Inventory Movement History grouped by Call-Off allocation.
     *
     * Beginning Inventory = total PPE physically received under Call-Off.
     * Actual Inventory    = PPE distributed to a project.
     * Ending Inventory    = Beginning - Actual.
     *
     * The previous row's ending balance becomes the next row's
     * beginning balance.
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
     * Build rows for one Call-Off provincial allocation.
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

        $callOff = $allocation
            ->distributionBatch
            ?->callOff;

        $purchaseOrder = $allocation
            ->distributionBatch
            ?->purchaseOrder;

        $supplier = $purchaseOrder
            ?->supplier;

        /*
         * Build the Call-Off's actual physically received PPE.
         *
         * Multiple Delivery Receipts are added together.
         *
         * Example:
         * DR-001 = 60
         * DR-002 = 40
         *
         * Beginning Call-Off Inventory = 100
         */
        $receivedByItem = $this
            ->receivedQuantities(
                $allocation
            );

        /*
         * Running balance begins from actual received quantities.
         */
        $runningBalance = $receivedByItem;

        /*
         * Projects must be processed chronologically.
         *
         * ID is the tie-breaker when multiple projects have
         * the same designation date.
         */
        $designations = $allocation
            ->supplyDesignations
            ->sortBy(
                fn ($designation): string => $designation
                    ->designation_date
                    ?->format('Y-m-d')
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
         * If no project designation exists yet, return one
         * opening Call-Off row.
         */
        if ($designations->isEmpty()) {
            return collect([
                $this->makeOpeningRow(
                    $allocation,
                    $receivedByItem
                ),
            ]);
        }

        $rows = collect();

        foreach (
            $designations as $designation
        ) {
            /*
             * Beginning is the current running Call-Off balance.
             */
            $beginning = $runningBalance;

            /*
             * Actual means PPE distributed to this project.
             */
            $actual = $this
                ->designationQuantities(
                    $designation
                );

            /*
             * Calculate ending balance per PPE item.
             */
            $ending = [];

            foreach (
                $this->itemIds(
                    $allocation,
                    $receivedByItem,
                    $actual
                ) as $itemId
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

            $rows->push([
                'province_distribution_id' => $allocation->id,

                'call_off_number' => $callOff?->call_off_number
                    ?? '—',

                'supplier_name' => $supplier?->supplier_name
                    ?? '—',

                /*
                 * Delivery Receipts remain available for audit.
                 */
                'delivery_receipts' => $allocation
                    ->deliveryReceipts
                    ->sortBy('delivery_date')
                    ->values(),

                'delivery_receipt_numbers' => $allocation
                    ->deliveryReceipts
                    ->sortBy('delivery_date')
                    ->pluck('dr_number')
                    ->filter()
                    ->implode(', '),

                'first_delivery_date' => $allocation
                    ->deliveryReceipts
                    ->min('delivery_date'),

                'last_delivery_date' => $allocation
                    ->deliveryReceipts
                    ->max('delivery_date'),

                /*
                 * Project information.
                 */
                'supply_designation_id' => $designation->id,

                'designation_number' => $designation
                    ->designation_number,

                'project_code' => $designation->project_code
                    ?? '—',

                'project_title' => $designation->project_title
                    ?? $designation->project_name
                    ?? '—',

                'location' => $designation->location
                    ?? '—',

                'number_of_beneficiaries' => (int) (
                    $designation
                        ->number_of_beneficiaries
                    ?? 0
                ),

                'number_of_days' => (int) (
                    $designation
                        ->number_of_days
                    ?? 0
                ),

                'movement_date' => $designation
                    ->designation_date,

                /*
                 * Your requested report balances.
                 */
                'beginning' => $beginning,

                'actual' => $actual,

                'ending' => $ending,

                /*
                 * Total quantities for modern summary UI.
                 */
                'beginning_total' => array_sum($beginning),

                'actual_total' => array_sum($actual),

                'ending_total' => array_sum($ending),

                'designation' => $designation,

                'allocation' => $allocation,
            ]);

            /*
             * CRITICAL RULE:
             *
             * Current ending becomes next project's beginning.
             */
            $runningBalance = $ending;
        }

        return $rows;
    }

    /**
     * Sum all Delivery Receipts under the Call-Off.
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
            foreach (
                $receipt->items as $receiptItem
            ) {
                $itemId = (int) $receiptItem->item_id;

                $quantity = (int) (
                    $receiptItem
                        ->received_quantity
                    ?? $receiptItem->quantity
                    ?? 0
                );

                if (! isset(
                    $quantities[$itemId]
                )) {
                    $quantities[$itemId] = 0;
                }

                $quantities[$itemId] +=
                    $quantity;
            }
        }

        return $quantities;
    }

    /**
     * PPE distributed to one project.
     *
     * @return array<int, int>
     */
    private function designationQuantities(
        $designation
    ): array {
        $quantities = [];

        foreach (
            $designation->items as $designationItem
        ) {
            $itemId = (int) $designationItem->item_id;

            $quantities[$itemId] =
                (int) $designationItem->quantity;
        }

        return $quantities;
    }

    /**
     * Get all PPE item IDs involved in this Call-Off row.
     *
     * @param  array<int, int>  $received
     * @param  array<int, int>  $actual
     * @return Collection<int, int>
     */
    private function itemIds(
        ProvinceDistribution $allocation,
        array $received,
        array $actual
    ): Collection {
        return collect(
            array_merge(
                $allocation
                    ->items
                    ->pluck('item_id')
                    ->map(
                        fn ($itemId): int => (int) $itemId
                    )
                    ->all(),

                array_keys($received),

                array_keys($actual)
            )
        )
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Opening Call-Off row when no project has been created.
     *
     * @param  array<int, int>  $received
     * @return array<string, mixed>
     */
    private function makeOpeningRow(
        ProvinceDistribution $allocation,
        array $received
    ): array {
        $callOff = $allocation
            ->distributionBatch
            ?->callOff;

        $supplier = $allocation
            ->distributionBatch
            ?->purchaseOrder
            ?->supplier;

        $zeroActual = [];

        foreach (
            array_keys($received) as $itemId
        ) {
            $zeroActual[$itemId] = 0;
        }

        return [
            'province_distribution_id' => $allocation->id,

            'call_off_number' => $callOff?->call_off_number
                ?? '—',

            'supplier_name' => $supplier?->supplier_name
                ?? '—',

            'delivery_receipts' => $allocation
                ->deliveryReceipts
                ->sortBy('delivery_date')
                ->values(),

            'delivery_receipt_numbers' => $allocation
                ->deliveryReceipts
                ->sortBy('delivery_date')
                ->pluck('dr_number')
                ->filter()
                ->implode(', '),

            'first_delivery_date' => $allocation
                ->deliveryReceipts
                ->min('delivery_date'),

            'last_delivery_date' => $allocation
                ->deliveryReceipts
                ->max('delivery_date'),

            'supply_designation_id' => null,

            'designation_number' => null,

            'project_code' => '—',

            'project_title' => 'No Project Distribution Yet',

            'location' => '—',

            'number_of_beneficiaries' => 0,

            'number_of_days' => 0,

            'movement_date' => $allocation
                ->deliveryReceipts
                ->max('delivery_date'),

            'beginning' => $received,

            'actual' => $zeroActual,

            'ending' => $received,

            'beginning_total' => array_sum($received),

            'actual_total' => 0,

            'ending_total' => array_sum($received),

            'designation' => null,

            'allocation' => $allocation,
        ];
    }
}
