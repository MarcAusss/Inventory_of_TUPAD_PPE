<?php

namespace App\Console\Commands;

use App\Models\DeliveryReceipt;
use App\Models\SupplyDesignation;
use App\Services\InventoryMovementService;
use Illuminate\Console\Command;
use Throwable;

class BackfillInventoryMovements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inventory:backfill-movements
                            {--province= : Restrict processing to one province ID}';

    /**
     * The console command description.
     */
    protected $description =
        'Create or update inventory movements from linked Delivery Receipts and Project PPE Designations.';

    public function handle(
        InventoryMovementService $inventoryMovementService
    ): int {
        $provinceId = $this->option('province')
            ? (int) $this->option('province')
            : null;

        $receiptProcessed = 0;
        $receiptSkipped = 0;
        $receiptFailed = 0;

        $designationProcessed = 0;
        $designationSkipped = 0;
        $designationFailed = 0;

        $this->info(
            'Backfilling Delivery Receipt inventory movements...'
        );

        DeliveryReceipt::query()
            ->with([
                'items.item',
                'provinceDistribution',
                'receivedByUser',
            ])
            ->where(
                'status',
                'Received'
            )
            ->when(
                $provinceId,
                fn ($query) => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function (
                    $receipts
                ) use (
                    $inventoryMovementService,
                    &$receiptProcessed,
                    &$receiptSkipped,
                    &$receiptFailed
                ): void {
                    foreach ($receipts as $receipt) {
                        /*
                         * New movement records require an exact
                         * Province Distribution / Call-Off source.
                         *
                         * Legacy receipts without this source cannot
                         * safely be assigned automatically.
                         */
                        if (! $receipt->province_distribution_id) {
                            $receiptSkipped++;

                            $this->warn(
                                "Skipped Delivery Receipt #{$receipt->id} "
                                ."({$receipt->dr_number}): "
                                .'no Province Distribution source.'
                            );

                            continue;
                        }

                        try {
                            $inventoryMovementService
                                ->recordDeliveryReceipt(
                                    $receipt
                                );

                            $receiptProcessed++;

                            $this->line(
                                "Processed Delivery Receipt #{$receipt->id} "
                                ."({$receipt->dr_number})."
                            );
                        } catch (Throwable $exception) {
                            $receiptFailed++;

                            $this->error(
                                "Failed Delivery Receipt #{$receipt->id} "
                                ."({$receipt->dr_number}): "
                                .$exception->getMessage()
                            );
                        }
                    }
                }
            );

        $this->newLine();

        $this->info(
            'Backfilling Project PPE Designation inventory movements...'
        );

        SupplyDesignation::query()
            ->with([
                'items.item',
                'provinceDistribution',
                'creator',
            ])
            ->where(
                'status',
                'Completed'
            )
            ->when(
                $provinceId,
                fn ($query) => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->orderBy(
                'designation_date'
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function (
                    $designations
                ) use (
                    $inventoryMovementService,
                    &$designationProcessed,
                    &$designationSkipped,
                    &$designationFailed
                ): void {
                    foreach ($designations as $designation) {
                        /*
                         * Legacy designations without a Call-Off source
                         * must not be guessed.
                         *
                         * They remain valid for pooled provincial stock,
                         * but cannot receive Call-Off movement snapshots.
                         */
                        if (! $designation->province_distribution_id) {
                            $designationSkipped++;

                            $projectReference =
                                $designation->project_code
                                ?: $designation->designation_number
                                ?: 'No project reference';

                            $this->warn(
                                "Skipped Project Designation #{$designation->id} "
                                ."({$projectReference}): "
                                .'no linked Call-Off allocation.'
                            );

                            continue;
                        }

                        try {
                            $inventoryMovementService
                                ->recordSupplyDesignation(
                                    $designation
                                );

                            $designationProcessed++;

                            $this->line(
                                "Processed Project Designation #{$designation->id} "
                                ."({$designation->project_code})."
                            );
                        } catch (Throwable $exception) {
                            $designationFailed++;

                            $this->error(
                                "Failed Project Designation #{$designation->id} "
                                ."({$designation->project_code}): "
                                .$exception->getMessage()
                            );
                        }
                    }
                }
            );

        $this->newLine();

        $this->table(
            [
                'Source',
                'Processed',
                'Skipped Legacy',
                'Failed',
            ],
            [
                [
                    'Delivery Receipts',
                    $receiptProcessed,
                    $receiptSkipped,
                    $receiptFailed,
                ],
                [
                    'Project Designations',
                    $designationProcessed,
                    $designationSkipped,
                    $designationFailed,
                ],
            ]
        );

        if (
            $receiptSkipped > 0
            || $designationSkipped > 0
        ) {
            $this->warn(
                'Legacy records were skipped because their true Call-Off source is unknown.'
            );

            $this->line(
                'Do not assign those records automatically unless their original Call-Off is confirmed.'
            );
        }

        if (
            $receiptFailed > 0
            || $designationFailed > 0
        ) {
            $this->error(
                'Backfill completed with one or more failures.'
            );

            return self::FAILURE;
        }

        $this->info(
            'Inventory movement backfill completed successfully.'
        );

        return self::SUCCESS;
    }
}
