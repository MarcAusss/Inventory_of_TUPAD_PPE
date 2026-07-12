<?php

namespace App\Console\Commands;

use App\Models\DeliveryReceiptItem;
use App\Models\Item;
use App\Models\ProvincialInventory;
use App\Models\Province;
use App\Models\SupplyDesignationItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileProvincialInventory extends Command
{
    protected $signature = 'inventory:reconcile-provincial
                            {--province= : Province ID to inspect}
                            {--apply : Update provincial inventory records}';

    protected $description =
        'Compare and optionally rebuild pooled provincial inventory from received PPE minus completed project designations.';

    public function handle(): int
    {
        $provinceId = $this->option('province')
            ? (int) $this->option('province')
            : null;

        $provinces = Province::query()
            ->when(
                $provinceId,
                fn ($query) => $query->whereKey($provinceId)
            )
            ->orderBy('id')
            ->get();

        if ($provinces->isEmpty()) {
            $this->error('No matching province was found.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');

        foreach ($provinces as $province) {
            $this->newLine();
            $this->info(
                "Province: {$province->name} (#{$province->id})"
            );

            $rows = [];

            foreach (
                Item::query()->orderBy('id')->get()
                as $item
            ) {
                $received = (int) DeliveryReceiptItem::query()
                    ->where('item_id', $item->id)
                    ->whereHas(
                        'deliveryReceipt',
                        fn ($query) => $query
                            ->where(
                                'province_id',
                                $province->id
                            )
                            ->where(
                                'status',
                                'Received'
                            )
                    )
                    ->sum('received_quantity');

                $distributed = (int) SupplyDesignationItem::query()
                    ->where('item_id', $item->id)
                    ->whereHas(
                        'supplyDesignation',
                        fn ($query) => $query
                            ->where(
                                'province_id',
                                $province->id
                            )
                            ->where(
                                'status',
                                'Completed'
                            )
                    )
                    ->sum('quantity');

                $expected = max(
                    0,
                    $received - $distributed
                );

                $inventory = ProvincialInventory::query()
                    ->where(
                        'province_id',
                        $province->id
                    )
                    ->where(
                        'item_id',
                        $item->id
                    )
                    ->first();

                $current = (int) (
                    $inventory?->quantity ?? 0
                );

                $rows[] = [
                    'item' => trim(
                        $item->item_name
                        .' '
                        .($item->label ?? '')
                    ),
                    'received' => $received,
                    'distributed' => $distributed,
                    'expected' => $expected,
                    'current' => $current,
                    'difference' => $expected - $current,
                ];
            }

            $this->table(
                [
                    'PPE Item',
                    'Received',
                    'Distributed',
                    'Expected',
                    'Current',
                    'Difference',
                ],
                array_map(
                    fn (array $row): array => [
                        $row['item'],
                        $row['received'],
                        $row['distributed'],
                        $row['expected'],
                        $row['current'],
                        $row['difference'],
                    ],
                    $rows
                )
            );

            if (! $apply) {
                continue;
            }

            DB::transaction(
                function () use (
                    $province,
                    $rows
                ): void {
                    $items = Item::query()
                        ->orderBy('id')
                        ->get()
                        ->values();

                    foreach (
                        $rows as $index => $row
                    ) {
                        $item = $items[$index];

                        ProvincialInventory::query()
                            ->updateOrCreate(
                                [
                                    'province_id' =>
                                        $province->id,

                                    'item_id' =>
                                        $item->id,
                                ],
                                [
                                    'quantity' =>
                                        $row['expected'],
                                ]
                            );
                    }
                }
            );

            $this->info(
                'Provincial inventory was reconciled.'
            );
        }

        if (! $apply) {
            $this->warn(
                'Inspection only. No records were changed.'
            );

            $this->line(
                'Run again with --apply after reviewing the results.'
            );
        }

        return self::SUCCESS;
    }
}