<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\InventoryMovement;
use App\Models\SupplyDesignation;

class InventoryMovementService
{
    /**
     * Record stock-in movements from a Delivery Receipt.
     */
    public function recordDeliveryReceipt(
        DeliveryReceipt $receipt
    ): void {
        $receipt->loadMissing([
            'items.item',
            'receivedByUser',
        ]);

        foreach ($receipt->items as $receiptItem) {
            $quantity = (int) $receiptItem->received_quantity;

            if ($quantity <= 0) {
                continue;
            }

            InventoryMovement::query()->updateOrCreate(
                [
                    'delivery_receipt_id' => $receipt->id,
                    'item_id' => $receiptItem->item_id,
                    'movement_type' => 'IN',
                ],
                [
                    'province_id' => $receipt->province_id,
                    'created_by' => $receipt->received_by_user_id,
                    'supply_designation_id' => null,
                    'quantity' => $quantity,
                    'movement_date' => $receipt->delivery_date,
                    'reference_number' => $receipt->dr_number,
                    'description' => 'PPE received through Delivery Receipt',
                    'remarks' => $receipt->remarks,
                ]
            );
        }
    }

    /**
     * Record stock-out movements from a Project PPE Designation.
     */
    public function recordSupplyDesignation(
        SupplyDesignation $designation
    ): void {
        $designation->loadMissing([
            'items.item',
            'creator',
        ]);

        foreach ($designation->items as $designationItem) {
            $quantity = (int) $designationItem->quantity;

            if ($quantity <= 0) {
                continue;
            }

            InventoryMovement::query()->updateOrCreate(
                [
                    'supply_designation_id' => $designation->id,
                    'item_id' => $designationItem->item_id,
                    'movement_type' => 'OUT',
                ],
                [
                    'province_id' => $designation->province_id,
                    'created_by' => $designation->created_by,
                    'delivery_receipt_id' => null,
                    'quantity' => $quantity,
                    'movement_date' => $designation->designation_date,
                    'reference_number' => $designation->project_code,
                    'description' => 'PPE distributed to project: '
                        .$designation->project_title,
                    'remarks' => $designation->remarks,
                ]
            );
        }
    }
}
