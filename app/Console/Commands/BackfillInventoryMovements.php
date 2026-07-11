<?php

namespace App\Console\Commands;

use App\Models\DeliveryReceipt;
use App\Models\SupplyDesignation;
use App\Services\InventoryMovementService;
use Illuminate\Console\Command;

class BackfillInventoryMovements extends Command
{
    protected $signature = 'inventory:backfill-movements';

    protected $description =
        'Create missing inventory movement records from existing Delivery Receipts and Project Designations.';

    public function handle(
        InventoryMovementService $movementService
    ): int {
        $receiptCount = 0;
        $designationCount = 0;

        DeliveryReceipt::query()
            ->with('items')
            ->chunkById(
                100,
                function ($receipts) use (
                    $movementService,
                    &$receiptCount
                ): void {
                    foreach ($receipts as $receipt) {
                        $movementService
                            ->recordDeliveryReceipt($receipt);

                        $receiptCount++;
                    }
                }
            );

        SupplyDesignation::query()
            ->with('items')
            ->whereNotNull('province_id')
            ->chunkById(
                100,
                function ($designations) use (
                    $movementService,
                    &$designationCount
                ): void {
                    foreach ($designations as $designation) {
                        $movementService
                            ->recordSupplyDesignation($designation);

                        $designationCount++;
                    }
                }
            );

        $this->info(
            "Processed {$receiptCount} Delivery Receipt(s)."
        );

        $this->info(
            "Processed {$designationCount} Project Designation(s)."
        );

        $this->info(
            'Inventory movement backfill completed.'
        );

        return self::SUCCESS;
    }
}
