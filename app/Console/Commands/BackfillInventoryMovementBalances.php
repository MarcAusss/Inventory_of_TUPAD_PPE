<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillInventoryMovementBalances extends Command
{
    protected $signature =
        'inventory:backfill-movement-balances';

    protected $description =
        'Calculate balance before and after for existing inventory movements.';

    public function handle(): int
    {
        $groups = InventoryMovement::query()
            ->select([
                'province_id',
                'item_id',
            ])
            ->distinct()
            ->get();

        foreach ($groups as $group) {
            DB::transaction(function () use ($group): void {
                $balance = 0;

                $movements = InventoryMovement::query()
                    ->where(
                        'province_id',
                        $group->province_id
                    )
                    ->where(
                        'item_id',
                        $group->item_id
                    )
                    ->orderBy('movement_date')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($movements as $movement) {
                    $balanceBefore = $balance;

                    if ($movement->isStockIn()) {
                        $balance +=
                            (int) $movement->quantity;
                    } else {
                        $balance -=
                            (int) $movement->quantity;
                    }

                    if ($balance < 0) {
                        $this->warn(
                            "Movement {$movement->id} produced a negative historical balance."
                        );
                    }

                    $movement->update([
                        'balance_before' =>
                            max(
                                0,
                                $balanceBefore
                            ),

                        'balance_after' =>
                            max(
                                0,
                                $balance
                            ),
                    ]);
                }
            });
        }

        $this->info(
            'Inventory movement balances were backfilled successfully.'
        );

        return self::SUCCESS;
    }
}