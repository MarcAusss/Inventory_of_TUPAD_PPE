<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\SupplyDesignation;
use Illuminate\Support\Collection;

class InventoryMovementReportService
{
    /**
     * Build the project-distribution inventory ledger for one exact
     * Delivery Receipt.
     *
     * Delivery Receipts under the same Call-Off are never combined.
     *
     * Beginning Inventory:
     * Available PPE from this receipt before the current project.
     *
     * Actual Distribution:
     * PPE distributed to the current project.
     *
     * Ending Inventory:
     * Beginning Inventory minus Actual Distribution.
     *
     * The current ending becomes the next row's beginning.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildForDeliveryReceipt(
        int $provinceId,
        int $deliveryReceiptId
    ): Collection {
        $receipt = DeliveryReceipt::query()
            ->with([
                'items.item',
                'receivedByUser',

                'provinceDistribution.items.item',

                'provinceDistribution.distributionBatch.callOff',

                'provinceDistribution.distributionBatch.purchaseOrder.supplier',

                'supplyDesignations' => function ($query): void {
                    $query
                        ->where(
                            'status',
                            'Completed'
                        )
                        ->orderBy(
                            'designation_date'
                        )
                        ->orderBy('id');
                },

                'supplyDesignations.items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->where(
                'status',
                'Received'
            )
            ->whereNotNull(
                'province_distribution_id'
            )
            ->whereKey(
                $deliveryReceiptId
            )
            ->firstOrFail();

        return $this->buildRows(
            $receipt
        );
    }

    /**
     * Build rows for the selected receipt.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRows(
        DeliveryReceipt $receipt
    ): Collection {
        $receipt->loadMissing([
            'items.item',
            'receivedByUser',

            'provinceDistribution.items.item',

            'provinceDistribution.distributionBatch.callOff',

            'provinceDistribution.distributionBatch.purchaseOrder.supplier',

            'supplyDesignations.items.item',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Initial receipt inventory
        |--------------------------------------------------------------------------
        |
        | The running balance begins with the PPE physically received
        | through this exact Delivery Receipt.
        |
        */

        $runningBalance = $this->receivedQuantities(
            $receipt
        );

        $designations = $receipt
            ->supplyDesignations
            ->where(
                'status',
                'Completed'
            )
            ->sortBy(
                function (
                    SupplyDesignation $designation
                ): string {
                    $date = $designation
                        ->designation_date
                        ?->format('Y-m-d')
                        ?? '0000-00-00';

                    return $date
                        .'|'
                        .str_pad(
                            (string) $designation->id,
                            20,
                            '0',
                            STR_PAD_LEFT
                        );
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | No project distribution yet
        |--------------------------------------------------------------------------
        |
        | Show one opening row so the selected receipt inventory can
        | still be inspected.
        |
        */

        if ($designations->isEmpty()) {
            $zeroDistribution =
                $this->emptyQuantities();

            return collect([
                $this->makeRow(
                    receipt: $receipt,
                    designation: null,
                    beginning: $runningBalance,
                    actualDistribution:
                        $zeroDistribution,
                    ending: $runningBalance
                ),
            ]);
        }

        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | Completed projects
        |--------------------------------------------------------------------------
        */

        foreach ($designations as $designation) {
            $beginning = $runningBalance;

            $actualDistribution =
                $this->designationQuantities(
                    $designation
                );

            $ending = $this->calculateEnding(
                beginning: $beginning,
                actualDistribution:
                    $actualDistribution
            );

            $rows->push(
                $this->makeRow(
                    receipt: $receipt,
                    designation: $designation,
                    beginning: $beginning,
                    actualDistribution:
                        $actualDistribution,
                    ending: $ending
                )
            );

            /*
             * The current ending inventory becomes the next project's
             * beginning inventory.
             */
            $runningBalance = $ending;
        }

        return $rows->values();
    }

    /**
     * Build one report row.
     *
     * @param array<int, int> $beginning
     * @param array<int, int> $actualDistribution
     * @param array<int, int> $ending
     *
     * @return array<string, mixed>
     */
    private function makeRow(
        DeliveryReceipt $receipt,
        ?SupplyDesignation $designation,
        array $beginning,
        array $actualDistribution,
        array $ending
    ): array {
        $allocation = $receipt
            ->provinceDistribution;

        $batch = $allocation
            ?->distributionBatch;

        $callOff = $batch
            ?->callOff;

        $purchaseOrder = $batch
            ?->purchaseOrder;

        $supplier = $purchaseOrder
            ?->supplier;

        return [
            /*
            |--------------------------------------------------------------------------
            | Delivery Receipt source
            |--------------------------------------------------------------------------
            */

            'delivery_receipt_id' =>
                (int) $receipt->id,

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

            /*
            |--------------------------------------------------------------------------
            | Parent Call-Off references
            |--------------------------------------------------------------------------
            */

            'province_distribution_id' =>
                (int) (
                    $receipt
                        ->province_distribution_id
                    ?? 0
                ),

            'call_off_number' =>
                $callOff?->call_off_number
                ?? '—',

            'purchase_order_number' =>
                $purchaseOrder?->po_number
                ?? '—',

            'supplier_name' =>
                $supplier?->supplier_name
                ?? '—',

            /*
            |--------------------------------------------------------------------------
            | Project details
            |--------------------------------------------------------------------------
            */

            'supply_designation_id' =>
                $designation?->id,

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

            'movement_date' =>
                $designation?->designation_date
                ?? $receipt->delivery_date,

            /*
            |--------------------------------------------------------------------------
            | Inventory values
            |--------------------------------------------------------------------------
            */

            'beginning' =>
                $beginning,

            /*
             * Keep the array key `actual` for Blade compatibility.
             * Its meaning is now Actual Distribution.
             */
            'actual' =>
                $actualDistribution,

            'ending' =>
                $ending,

            'beginning_total' =>
                array_sum($beginning),

            'actual_total' =>
                array_sum($actualDistribution),

            'ending_total' =>
                array_sum($ending),

            /*
            |--------------------------------------------------------------------------
            | Source models
            |--------------------------------------------------------------------------
            */

            'receipt' =>
                $receipt,

            'designation' =>
                $designation,

            'allocation' =>
                $allocation,
        ];
    }

    /**
     * Get PPE physically received in this exact receipt.
     *
     * @return array<int, int>
     */
    private function receivedQuantities(
        DeliveryReceipt $receipt
    ): array {
        $quantities = $this->emptyQuantities();

        foreach ($receipt->items as $receiptItem) {
            $itemId = (int) $receiptItem->item_id;

            if (! array_key_exists(
                $itemId,
                $quantities
            )) {
                continue;
            }

            $quantity = (int) (
                $receiptItem->received_quantity
                ?? $receiptItem->quantity
                ?? 0
            );

            if ($quantity <= 0) {
                continue;
            }

            $quantities[$itemId] +=
                $quantity;
        }

        return $quantities;
    }

    /**
     * Get PPE distributed through one project designation.
     *
     * @return array<int, int>
     */
    private function designationQuantities(
        SupplyDesignation $designation
    ): array {
        $quantities = $this->emptyQuantities();

        foreach ($designation->items as $item) {
            $itemId = (int) $item->item_id;

            if (! array_key_exists(
                $itemId,
                $quantities
            )) {
                continue;
            }

            $quantity = (int) $item->quantity;

            if ($quantity <= 0) {
                continue;
            }

            $quantities[$itemId] +=
                $quantity;
        }

        return $quantities;
    }

    /**
     * Ending = Beginning - Actual Distribution.
     *
     * @param array<int, int> $beginning
     * @param array<int, int> $actualDistribution
     *
     * @return array<int, int>
     */
    private function calculateEnding(
        array $beginning,
        array $actualDistribution
    ): array {
        $ending = $this->emptyQuantities();

        foreach (
            array_keys($ending)
            as $itemId
        ) {
            $ending[$itemId] = max(
                0,
                (int) (
                    $beginning[$itemId]
                    ?? 0
                )
                - (int) (
                    $actualDistribution[$itemId]
                    ?? 0
                )
            );
        }

        return $ending;
    }

    /**
     * The seven fixed PPE variants.
     *
     * @return array<int, int>
     */
    private function emptyQuantities(): array
    {
        return [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
        ];
    }
}