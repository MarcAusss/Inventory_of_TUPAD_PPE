<?php

namespace App\Console\Commands;

use App\Models\DeliveryReceiptItem;
use App\Models\InventoryMovement;
use App\Models\SupplyDesignation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillCallOffMovementBalances extends Command
{
    protected $signature =
        'inventory:backfill-calloff-balances
         {--province= : Restrict the backfill to one province ID}';

    protected $description =
        'Backfill Call-Off-specific beginning and ending balances for linked inventory movements.';

    public function handle(): int
    {
        $provinceId = $this->option('province')
            ? (int) $this->option('province')
            : null;

        $designations = SupplyDesignation::query()
            ->with([
                'items',
                'provinceDistribution',
            ])
            ->whereNotNull(
                'province_distribution_id'
            )
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
                'province_distribution_id'
            )
            ->orderBy(
                'designation_date'
            )
            ->orderBy('id')
            ->get()
            ->groupBy(
                'province_distribution_id'
            );

        $updated = 0;
        $missingMovements = 0;

        foreach (
            $designations as $allocationId => $allocationDesignations
        ) {
            $receivedByItem =
                $this->receivedByItem(
                    (int) $allocationId
                );

            $runningBalance =
                $receivedByItem->all();

            foreach (
                $allocationDesignations as $designation
            ) {
                foreach (
                    $designation->items as $designationItem
                ) {
                    $itemId =
                        (int) $designationItem->item_id;

                    $quantity =
                        (int) $designationItem->quantity;

                    $before = max(
                        0,
                        (int) (
                            $runningBalance[$itemId]
                            ?? 0
                        )
                    );

                    $after = max(
                        0,
                        $before - $quantity
                    );

                    $movement = InventoryMovement::query()
                        ->where(
                            'supply_designation_id',
                            $designation->id
                        )
                        ->where(
                            'item_id',
                            $itemId
                        )
                        ->where(
                            'movement_type',
                            'OUT'
                        )
                        ->first();

                    if (! $movement) {
                        $missingMovements++;

                        $this->warn(
                            "No OUT movement found for designation #{$designation->id}, item #{$itemId}."
                        );

                        $runningBalance[$itemId] =
                            $after;

                        continue;
                    }

                    $movement->update([
                        'province_distribution_id' => $designation
                            ->province_distribution_id,

                        'call_off_balance_before' => $before,

                        'call_off_balance_after' => $after,
                    ]);

                    $runningBalance[$itemId] =
                        $after;

                    $updated++;
                }
            }
        }

        $this->info(
            "Updated movement rows: {$updated}"
        );

        $this->line(
            "Missing movement rows: {$missingMovements}"
        );

        return self::SUCCESS;
    }

    /**
     * @return Collection<int|string, int>
     */
    private function receivedByItem(
        int $provinceDistributionId
    ): Collection {
        return DeliveryReceiptItem::query()
            ->whereHas(
                'deliveryReceipt',
                fn ($query) => $query
                    ->where(
                        'province_distribution_id',
                        $provinceDistributionId
                    )
                    ->where(
                        'status',
                        'Received'
                    )
            )
            ->selectRaw(
                'item_id, SUM(received_quantity) AS total_received'
            )
            ->groupBy('item_id')
            ->pluck(
                'total_received',
                'item_id'
            )
            ->map(
                fn ($quantity): int => (int) $quantity
            );
    }
}
