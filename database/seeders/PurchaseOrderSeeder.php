<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();
        $users = User::all();
        $items = Item::all();

        if ($suppliers->isEmpty() || $users->isEmpty() || $items->isEmpty()) {
            $this->command->warn('Suppliers, Users, or Items table is empty.');

            return;
        }

        for ($i = 1; $i <= 10; $i++) {

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $suppliers->random()->id,
                'created_by' => $users->random()->id,
                'po_number' => 'PO-' . now()->format('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'po_date' => now()->subDays(rand(1, 30)),
                'nefa_number' => 'NEFA-' . strtoupper(Str::random(6)),
                'total_amount' => 0,
                'document' => null,
                'status' => collect([
                    'Pending Distribution',
                    'Distributed',
                    'Completed',
                ])->random(),
                'remarks' => fake()->optional()->sentence(),
            ]);

            $totalAmount = 0;

            // Add ALL PPE items to every Purchase Order
            foreach ($items as $item) {

                $quantity = rand(1, 20);
                $unitCost = rand(100, 5000);
                $totalCost = $quantity * $unitCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,

                    // Use the item's label as the size (Medium, Large, US9, etc.)
                    'size' => $item->label,
                ]);

                $totalAmount += $totalCost;
            }

            $purchaseOrder->update([
                'total_amount' => $totalAmount,
            ]);
        }
    }
}