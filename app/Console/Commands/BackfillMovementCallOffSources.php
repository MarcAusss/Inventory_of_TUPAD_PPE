<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use Illuminate\Console\Command;

class BackfillMovementCallOffSources extends Command
{
    protected $signature =
        'inventory:backfill-calloff-sources';

    protected $description =
        'Backfill Province Distribution references on inventory movements.';

    public function handle(): int
    {
        $updated = 0;
        $unresolved = 0;

        InventoryMovement::query()
            ->whereNull(
                'province_distribution_id'
            )
            ->with([
                'deliveryReceipt',
                'supplyDesignation',
            ])
            ->orderBy('id')
            ->chunkById(
                100,
                function ($movements) use (
                    &$updated,
                    &$unresolved
                ): void {
                    foreach (
                        $movements as $movement
                    ) {
                        $provinceDistributionId = null;

                        if (
                            $movement->delivery_receipt_id
                            && $movement->deliveryReceipt
                        ) {
                            $provinceDistributionId =
                                $movement
                                    ->deliveryReceipt
                                    ->province_distribution_id;
                        }

                        if (
                            ! $provinceDistributionId
                            && $movement->supply_designation_id
                            && $movement->supplyDesignation
                        ) {
                            $provinceDistributionId =
                                $movement
                                    ->supplyDesignation
                                    ->province_distribution_id;
                        }

                        if (! $provinceDistributionId) {
                            $unresolved++;

                            $this->warn(
                                "Movement #{$movement->id} could not be linked."
                            );

                            continue;
                        }

                        $movement->update([
                            'province_distribution_id' => $provinceDistributionId,
                        ]);

                        $updated++;
                    }
                }
            );

        $this->info(
            "Updated movements: {$updated}"
        );

        $this->line(
            "Unresolved legacy movements: {$unresolved}"
        );

        return self::SUCCESS;
    }
}
