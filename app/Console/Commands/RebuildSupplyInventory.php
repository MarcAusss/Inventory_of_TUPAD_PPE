<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RebuildSupplyInventory extends Command
{
    protected $signature = 'inventory:rebuild-supply
                            {--force : Run without confirmation}';

    protected $description = 'Rebuild Supply inventory using only Purchase Orders not distributed to provincial offices';

    public function handle(): int
    {
        $this->newLine();
        $this->info('Supply Inventory Rebuild');
        $this->line(
            'Only Purchase Orders without provincial distributions will be included.'
        );
        $this->newLine();

        if (
            ! $this->option('force')
            && ! $this->confirm('Do you want to continue?')
        ) {
            $this->warn('Inventory rebuild cancelled.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function (): void {
                /*
                |--------------------------------------------------------------------------
                | Set every PPE inventory quantity to zero
                |--------------------------------------------------------------------------
                */

                Item::query()
                    ->orderBy('id')
                    ->each(function (Item $item): void {
                        Inventory::query()->updateOrCreate(
                            [
                                'item_id' => $item->id,
                            ],
                            [
                                'quantity' => 0,
                            ]
                        );
                    });

                /*
                |--------------------------------------------------------------------------
                | Get quantities from completely undistributed Purchase Orders
                |--------------------------------------------------------------------------
                |
                | A Purchase Order is excluded when it already has at least one:
                |
                | Purchase Order
                |   -> TSSD Distribution Batch
                |       -> Province Distribution
                |
                */

                $undistributedQuantities = DB::table(
                    'purchase_order_items'
                )
                    ->join(
                        'purchase_orders',
                        'purchase_orders.id',
                        '=',
                        'purchase_order_items.purchase_order_id'
                    )
                    ->whereNotExists(function ($query): void {
                        $query
                            ->selectRaw('1')
                            ->from('tssd_distribution_batches')
                            ->join(
                                'province_distributions',
                                'province_distributions.tssd_distribution_batch_id',
                                '=',
                                'tssd_distribution_batches.id'
                            )
                            ->whereColumn(
                                'tssd_distribution_batches.purchase_order_id',
                                'purchase_orders.id'
                            )
                            ->where(
                                'province_distributions.status',
                                '!=',
                                'Cancelled'
                            );
                    })
                    ->select(
                        'purchase_order_items.item_id',
                        DB::raw(
                            'SUM(purchase_order_items.quantity) AS total_quantity'
                        )
                    )
                    ->groupBy(
                        'purchase_order_items.item_id'
                    )
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Save the correct available Supply inventory
                |--------------------------------------------------------------------------
                */

                foreach ($undistributedQuantities as $quantity) {
                    Inventory::query()->updateOrCreate(
                        [
                            'item_id' => $quantity->item_id,
                        ],
                        [
                            'quantity' => (int) $quantity->total_quantity,
                        ]
                    );
                }
            });

            $this->newLine();
            $this->info('Supply inventory rebuilt successfully.');
            $this->newLine();

            $rows = Item::query()
                ->with('inventory')
                ->orderBy('item_name')
                ->orderBy('label')
                ->get()
                ->map(function (Item $item): array {
                    return [
                        $item->item_name,
                        $item->label ?: 'Standard',
                        $item->unit_of_measurement ?: '—',
                        number_format(
                            (int) ($item->inventory?->quantity ?? 0)
                        ),
                    ];
                })
                ->all();

            $this->table(
                [
                    'PPE Item',
                    'Variant',
                    'Unit',
                    'Available Quantity',
                ],
                $rows
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->newLine();
            $this->error('Supply inventory rebuild failed.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}