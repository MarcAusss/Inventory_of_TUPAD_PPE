<?php

namespace Tests\Feature;

use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\Province;
use App\Models\ProvinceDistribution;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\TssdDistributionBatch;
use App\Models\User;
use App\Services\CallOffInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CallOffInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_receipt_card_counts_received_receipts_with_available_ppe_on_the_current_page(): void
    {
        $province = Province::query()->create([
            'name' => 'Test Province',
        ]);

        $role = Role::query()->create([
            'name' => Role::PROVINCIAL,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'province_id' => $province->id,
            'username' => 'provincial-test-user',
        ]);

        $supplier = Supplier::query()->create([
            'supplier_name' => 'Test PPE Supplier',
            'contact_person' => 'Test Contact',
            'contact_number' => '09170000000',
            'address' => 'Test Address',
        ]);

        $allocations = collect();

        for ($sequence = 0; $sequence < 11; $sequence++) {
            $allocations->push(
                $this->createAllocation(
                    $province,
                    $supplier,
                    $user,
                    $sequence,
                    now()->subDays(20 - $sequence)
                )
            );
        }

        /*
         * This allocation is the oldest and therefore lands on page two.
         * Its received receipt must not be included in the page-one card.
         */
        $this->createReceipt(
            $allocations[0],
            'DR-OFF-PAGE',
            'Received'
        );

        /*
         * A received receipt on an allocation with no safe stock is excluded.
         */
        $this->createReceipt(
            $allocations[1],
            'DR-NO-STOCK',
            'Received'
        );

        /*
         * Only the received receipt is counted on this available allocation.
         */
        $this->createReceipt(
            $allocations[9],
            'DR-AVAILABLE-ONE',
            'Received'
        );

        $this->createReceipt(
            $allocations[9],
            'DR-AVAILABLE-PENDING',
            'Pending'
        );

        /*
         * Both received receipts are counted on this available allocation.
         */
        $this->createReceipt(
            $allocations[10],
            'DR-AVAILABLE-TWO-A',
            'Received'
        );

        $this->createReceipt(
            $allocations[10],
            'DR-AVAILABLE-TWO-B',
            'Received'
        );

        $availableAllocationIds = [
            $allocations[0]->id,
            $allocations[9]->id,
            $allocations[10]->id,
        ];

        $inventoryService = Mockery::mock(
            CallOffInventoryService::class
        );

        $inventoryService
            ->shouldReceive('balances')
            ->times(10)
            ->andReturnUsing(
                function (
                    ProvinceDistribution $allocation
                ) use (
                    $availableAllocationIds
                ): array {
                    $available = in_array(
                        $allocation->id,
                        $availableAllocationIds,
                        true
                    ) ? 5 : 0;

                    return [
                        1 => [
                            'actual_received' => $available,
                            'previously_distributed' => 0,
                            'call_off_available' => $available,
                            'available_for_projects' => $available,
                        ],
                    ];
                }
            );

        $this->app->instance(
            CallOffInventoryService::class,
            $inventoryService
        );

        $response = $this
            ->actingAs($user)
            ->get(route('provincial.call-off-inventory.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'summary',
                fn (array $summary): bool => $summary['available_delivery_receipt_count'] === 3
            )
            ->assertSeeText('Delivery Receipts with Available PPEs')
            ->assertDontSeeText('Call-Off Remaining');
    }

    private function createAllocation(
        Province $province,
        Supplier $supplier,
        User $user,
        int $sequence,
        mixed $receivedAt
    ): ProvinceDistribution {
        $purchaseOrder = PurchaseOrder::query()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'po_number' => "PO-TEST-{$sequence}",
            'po_date' => now()->toDateString(),
            'nefa_number' => "NEFA-TEST-{$sequence}",
            'status' => 'Distributed',
        ]);

        $batch = TssdDistributionBatch::query()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'created_by' => $user->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Approved',
        ]);

        CallOff::query()->create([
            'tssd_distribution_batch_id' => $batch->id,
            'purchase_order_id' => $purchaseOrder->id,
            'call_off_number' => "CO-TEST-{$sequence}",
            'assigned_by' => $user->id,
            'status' => 'Approved',
        ]);

        return ProvinceDistribution::query()->create([
            'tssd_distribution_batch_id' => $batch->id,
            'province_id' => $province->id,
            'status' => 'Received',
            'received_at' => $receivedAt,
        ]);
    }

    private function createReceipt(
        ProvinceDistribution $allocation,
        string $number,
        string $status
    ): void {
        DeliveryReceipt::query()->create([
            'province_distribution_id' => $allocation->id,
            'purchase_order_id' => $allocation
                ->distributionBatch
                ->purchase_order_id,
            'province_id' => $allocation->province_id,
            'dr_number' => $number,
            'delivery_date' => now()->toDateString(),
            'received_by' => 'Test Receiver',
            'status' => $status,
        ]);
    }
}
