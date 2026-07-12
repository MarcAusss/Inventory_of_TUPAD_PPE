<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\InventoryMovement;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    /**
     * Record stock-in movements from one Delivery Receipt.
     *
     * balance_before and balance_after represent the pooled provincial
     * inventory. province_distribution_id identifies the exact Call-Off
     * allocation that produced the stock.
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
            $receipt->province_distribution_id
            ?: $receipt
                ->provinceDistribution
                ?->id;

        if (! $provinceDistributionId) {
            throw ValidationException::withMessages([
                'province_distribution' => 'The Delivery Receipt has no linked Call-Off allocation.',
            ]);
        }

        foreach (
            $receipt->items as $receiptItem
        ) {
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
                    $receiptItem->item_id
                )
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    "items.{$receiptItem->item_id}" => 'The provincial inventory record could not be found after receiving the PPE.',
                ]);
            }

            /*
             * ReceivingService has already increased ProvincialInventory
             * before this method runs.
             */
            $balanceAfter =
                (int) $inventory->quantity;

            $balanceBefore = max(
                0,
                $balanceAfter - $quantity
            );

            InventoryMovement::query()
                ->updateOrCreate(
                    [
                        'delivery_receipt_id' => $receipt->id,

                        'item_id' => $receiptItem->item_id,

                        'movement_type' => 'IN',
                    ],
                    [
                        'province_id' => $receipt->province_id,

                        'province_distribution_id' => $provinceDistributionId,

                        'created_by' => $receipt->received_by_user_id,

                        'supply_designation_id' => null,

                        'quantity' => $quantity,

                        /*
                         * Province-wide pooled inventory balances.
                         */
                        'balance_before' => $balanceBefore,

                        'balance_after' => $balanceAfter,

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
     * balance_before and balance_after remain pooled provincial inventory
     * balances. The Call-Off report will separately calculate its own
     * beginning and ending balances using province_distribution_id.
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
            $designation->province_distribution_id
            ?: $designation
                ->provinceDistribution
                ?->id;

        if (! $provinceDistributionId) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'The Project PPE Designation has no linked Call-Off allocation.',
            ]);
        }

        foreach (
            $designation->items as $designationItem
        ) {
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
                    $designationItem->item_id
                )
                ->first();

            if (! $inventory) {
                throw ValidationException::withMessages([
                    "items.{$designationItem->item_id}" => 'The provincial inventory record could not be found after the project distribution.',
                ]);
            }

            /*
             * SupplyDesignationService already deducted the pooled stock.
             */
            $balanceAfter =
                (int) $inventory->quantity;

            $balanceBefore =
                $balanceAfter + $quantity;

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

                        'item_id' => $designationItem->item_id,

                        'movement_type' => 'OUT',
                    ],
                    [
                        'province_id' => $designation->province_id,

                        'province_distribution_id' => $provinceDistributionId,

                        'created_by' => $designation->created_by,

                        'delivery_receipt_id' => null,

                        'quantity' => $quantity,

                        /*
                         * Province-wide pooled inventory balances.
                         */
                        'balance_before' => $balanceBefore,

                        'balance_after' => $balanceAfter,

                        'movement_date' => $designation->designation_date,

                        /*
                         * The direct OUT reference remains the project code.
                         */
                        'reference_number' => $designation->project_code,

                        'description' => $description,

                        'remarks' => $designation->remarks,
                    ]
                );
        }
    }
}
