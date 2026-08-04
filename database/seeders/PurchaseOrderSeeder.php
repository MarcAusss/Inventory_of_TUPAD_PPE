<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $supplyUser = User::query()->where('username', 'supply')->first();

        if (! $supplyUser) {
            throw new RuntimeException('Supply Unit user is required before PurchaseOrderSeeder runs.');
        }

        $items = Item::query()->where('is_active', true)->orderForDisplay()->get();

        if ($items->isEmpty()) {
            throw new RuntimeException('PPE items are required before PurchaseOrderSeeder runs.');
        }

        $definitions = [
            [
                'po_number' => 'PO-2026-0001',
                'po_date' => '2026-01-20',
                'nefa_number' => 'NEFA-2026-001',
                'supplier' => 'ABC Safety Supplies',
                'quantity_per_item' => 5000,
                'status' => 'Distributed',
                'remarks' => 'Demo PO: two Call-Offs with multiple Delivery Receipts per province.',
            ],
            [
                'po_number' => 'PO-2026-0002',
                'po_date' => '2026-04-15',
                'nefa_number' => 'NEFA-2026-002',
                'supplier' => 'Bicol Industrial Trading',
                'quantity_per_item' => 4200,
                'status' => 'Distributed',
                'remarks' => 'Demo PO: active/partial receiving workflow plus a pending Call-Off request.',
            ],
            [
                'po_number' => 'PO-2026-0003',
                'po_date' => '2026-07-01',
                'nefa_number' => 'NEFA-2026-003',
                'supplier' => 'SafeWear Philippines',
                'quantity_per_item' => 3000,
                'status' => 'Pending Distribution',
                'remarks' => 'Demo PO: remains in Supply Unit inventory for available-stock testing.',
            ],
        ];

        DB::transaction(function () use ($definitions, $supplyUser, $items): void {
            foreach ($definitions as $definition) {
                $supplier = Supplier::query()
                    ->where('supplier_name', $definition['supplier'])
                    ->first();

                if (! $supplier) {
                    throw new RuntimeException("Missing supplier: {$definition['supplier']}");
                }

                $purchaseOrder = PurchaseOrder::query()->updateOrCreate(
                    ['po_number' => $definition['po_number']],
                    [
                        'supplier_id' => $supplier->id,
                        'created_by' => $supplyUser->id,
                        'po_date' => $definition['po_date'],
                        'nefa_number' => $definition['nefa_number'],
                        'total_amount' => 0,
                        'document' => null,
                        'status' => $definition['status'],
                        'remarks' => $definition['remarks'],
                    ]
                );

                $totalAmount = 0.0;

                foreach ($items as $index => $item) {
                    $unitCost = 180 + ($index * 35);
                    $quantity = (int) $definition['quantity_per_item'];
                    $totalCost = $quantity * $unitCost;

                    PurchaseOrderItem::query()->updateOrCreate(
                        [
                            'purchase_order_id' => $purchaseOrder->id,
                            'item_id' => $item->id,
                        ],
                        [
                            'quantity' => $quantity,
                            'unit_cost' => $unitCost,
                            'total_cost' => $totalCost,
                            'size' => $item->label,
                        ]
                    );

                    $totalAmount += $totalCost;
                }

                $purchaseOrder->update(['total_amount' => $totalAmount]);
            }

            // PurchaseOrderController normally increments this table on create.
            // Seed the same initial state; DistributionWorkflowSeeder will then
            // synchronize it to the final undistributed Supply stock.
            DB::table('inventory')->delete();

            foreach ($items as $item) {
                $ordered = (int) PurchaseOrderItem::query()
                    ->where('item_id', $item->id)
                    ->sum('quantity');

                DB::table('inventory')->insert([
                    'item_id' => $item->id,
                    'quantity' => $ordered,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
