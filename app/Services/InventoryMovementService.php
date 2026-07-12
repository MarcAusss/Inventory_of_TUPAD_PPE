<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptItem;
use App\Models\InventoryMovement;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use App\Models\SupplyDesignationItem;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    /**
     * Record a stock-in movement from one Delivery Receipt.
     *
     * Province-wide balance:
     * Current pooled inventory after receipt.
     *
     * Call-Off balance:
     * Total physically received under the selected Call-Off,
     * including the current Delivery Receipt.
     */
    public function recordDeliveryReceipt(
        DeliveryReceipt $receipt
    ): void {
        $receipt->loadMissing([
            'items.item',
            'receivedByUser',

            'provinceDistribution'
                .'.distributionBatch'
                .'.callOff',

            'provinceDistribution'
                .'.distributionBatch'
                .'.purchaseOrder'
                .'.supplier',
        ]);

        $provinceDistributionId =
            (int) (
                $receipt->province_distribution_id
                ?: $receipt
                    ->provinceDistribution
                    ?->id
            );

        if ($provinceDistributionId <= 0) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'The Delivery Receipt has no linked Call-Off allocation.',
            ]);
        }

        foreach (
            $receipt->items as $receiptItem
        ) {
            $itemId =
                (int) $receiptItem->item_id;

            $quantity = (int) (
                $receiptItem->received_quantity
                ?? $receiptItem->quantity
                ?? 0
            );

            if ($quantity <= 0) {
                continue;
            }

            $inventory = ProvincialInventory::query()
                ->where(
                    'province_id',
                    $receipt->province_id
                )
                ->where(
                    'item_id',
                    $itemId
                )
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    "items.{$itemId}" => 'The provincial inventory record could not be found after receiving the PPE.',
                ]);
            }

            /*
             * ReceivingService has already increased the pooled stock.
             */
            $pooledBalanceAfter =
                (int) $inventory->quantity;

            $pooledBalanceBefore = max(
                0,
                $pooledBalanceAfter - $quantity
            );

            /*
             * Get the exact Call-Off received balance after this DR.
             *
             * The current receipt is already stored when this method runs.
             */
            $callOffBalanceAfter =
                $this->receivedQuantityForCallOff(
                    $provinceDistributionId,
                    $itemId
                );

            $callOffBalanceBefore = max(
                0,
                $callOffBalanceAfter - $quantity
            );

            InventoryMovement::query()
                ->updateOrCreate(
                    [
                        'delivery_receipt_id' => $receipt->id,

                        'item_id' => $itemId,

                        'movement_type' => 'IN',
                    ],
                    [
                        'province_id' => $receipt->province_id,

                        'province_distribution_id' => $provinceDistributionId,

                        'created_by' => $receipt->received_by_user_id,

                        'supply_designation_id' => null,

                        'quantity' => $quantity,

                        /*
                         * Province-wide balances.
                         */
                        'balance_before' => $pooledBalanceBefore,

                        'balance_after' => $pooledBalanceAfter,

                        /*
                         * Call-Off received balances.
                         */
                        'call_off_balance_before' => $callOffBalanceBefore,

                        'call_off_balance_after' => $callOffBalanceAfter,

                        'movement_date' => $receipt->delivery_date,

                        'reference_number' => $receipt->dr_number,

                        'description' => 'PPE received through Delivery Receipt',

                        'remarks' => $receipt->remarks,
                    ]
                );
        }
    }

    /**
     * Record stock-out movements from one Project PPE Designation.
     *
     * Call-Off beginning:
     * Actual received under the Call-Off
     * - project quantities previously distributed under the Call-Off
     * - excluding the current designation
     *
     * Call-Off ending:
     * Call-Off beginning - current project quantity
     */
    public function recordSupplyDesignation(
        SupplyDesignation $designation
    ): void {
        $designation->loadMissing([
            'items.item',
            'creator',

            'provinceDistribution'
                .'.distributionBatch'
                .'.callOff',

            'provinceDistribution'
                .'.distributionBatch'
                .'.purchaseOrder'
                .'.supplier',
        ]);

        $provinceDistributionId =
            (int) (
                $designation->province_distribution_id
                ?: $designation
                    ->provinceDistribution
                    ?->id
            );

        if ($provinceDistributionId <= 0) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'The Project PPE Designation has no linked Call-Off allocation.',
            ]);
        }

        foreach (
            $designation->items as $designationItem
        ) {
            $itemId =
                (int) $designationItem->item_id;

            $quantity =
                (int) $designationItem->quantity;

            if ($quantity <= 0) {
                continue;
            }

            $inventory = ProvincialInventory::query()
                ->where(
                    'province_id',
                    $designation->province_id
                )
                ->where(
                    'item_id',
                    $itemId
                )
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    "items.{$itemId}" => 'The provincial inventory record could not be found after the project distribution.',
                ]);
            }

            /*
             * SupplyDesignationService already deducted pooled stock.
             */
            $pooledBalanceAfter =
                (int) $inventory->quantity;

            $pooledBalanceBefore =
                $pooledBalanceAfter + $quantity;

            /*
             * Total physically received for this exact Call-Off.
             */
            $actualReceived =
                $this->receivedQuantityForCallOff(
                    $provinceDistributionId,
                    $itemId
                );

            /*
             * Total project distribution before the current designation.
             */
            $distributedBeforeCurrent =
                $this->distributedQuantityBeforeDesignation(
                    $provinceDistributionId,
                    $itemId,
                    $designation->id
                );

            $callOffBalanceBefore = max(
                0,
                $actualReceived
                    - $distributedBeforeCurrent
            );

            $callOffBalanceAfter = max(
                0,
                $callOffBalanceBefore
                    - $quantity
            );

            $callOffNumber = $designation
                ->provinceDistribution
                ?->distributionBatch
                ?->callOff
                ?->call_off_number;

            $description =
                'PPE distributed to project: '
                .$designation->project_title;

            if ($callOffNumber) {
                $description .=
                    " under Call-Off {$callOffNumber}";
            }

            InventoryMovement::query()
                ->updateOrCreate(
                    [
                        'supply_designation_id' => $designation->id,

                        'item_id' => $itemId,

                        'movement_type' => 'OUT',
                    ],
                    [
                        'province_id' => $designation->province_id,

                        'province_distribution_id' => $provinceDistributionId,

                        'created_by' => $designation->created_by,

                        'delivery_receipt_id' => null,

                        'quantity' => $quantity,

                        /*
                         * Province-wide pooled balances.
                         */
                        'balance_before' => $pooledBalanceBefore,

                        'balance_after' => $pooledBalanceAfter,

                        /*
                         * Selected Call-Off balances.
                         */
                        'call_off_balance_before' => $callOffBalanceBefore,

                        'call_off_balance_after' => $callOffBalanceAfter,

                        'movement_date' => $designation->designation_date,

                        /*
                         * Direct OUT reference remains project code.
                         */
                        'reference_number' => $designation->project_code,

                        'description' => $description,

                        'remarks' => $designation->remarks,
                    ]
                );
        }
    }

    /**
     * Sum actual PPE received under one Call-Off allocation.
     */
    private function receivedQuantityForCallOff(
        int $provinceDistributionId,
        int $itemId
    ): int {
        return (int) DeliveryReceiptItem::query()
            ->where(
                'item_id',
                $itemId
            )
            ->whereHas(
                'deliveryReceipt',
                function ($query) use (
                    $provinceDistributionId
                ): void {
                    $query
                        ->where(
                            'province_distribution_id',
                            $provinceDistributionId
                        )
                        ->where(
                            'status',
                            'Received'
                        );
                }
            )
            ->sum(
                'received_quantity'
            );
    }

    /**
     * Sum completed project designations before the current project.
     *
     * The current designation is excluded because its item rows already
     * exist before InventoryMovementService is called.
     */
    private function distributedQuantityBeforeDesignation(
        int $provinceDistributionId,
        int $itemId,
        int $currentDesignationId
    ): int {
        return (int) SupplyDesignationItem::query()
            ->where(
                'item_id',
                $itemId
            )
            ->whereHas(
                'supplyDesignation',
                function ($query) use (
                    $provinceDistributionId,
                    $currentDesignationId
                ): void {
                    $query
                        ->where(
                            'province_distribution_id',
                            $provinceDistributionId
                        )
                        ->where(
                            'status',
                            'Completed'
                        )
                        ->where(
                            'id',
                            '!=',
                            $currentDesignationId
                        );
                }
            )
            ->sum('quantity');
    }
}
