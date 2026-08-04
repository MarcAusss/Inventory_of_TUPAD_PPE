<?php

namespace Database\Seeders;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DistributionWorkflowSeeder extends Seeder
{
    private const DEMO_PREFIX = 'CO-2026-DEMO';

    /**
     * Base allocation per PPE item for each province. The same quantity is used
     * for every PPE variant inside a single provincial Call-Off allocation so
     * the test data stays easy to verify manually.
     */
    private const PROVINCE_BASE = [
        'Albay' => 240,
        'Camarines Norte' => 180,
        'Camarines Sur' => 260,
        'Catanduanes' => 150,
        'Masbate' => 190,
        'Sorsogon' => 220,
    ];

    private const PROVINCE_CODE = [
        'Albay' => 'ALB',
        'Camarines Norte' => 'CAN',
        'Camarines Sur' => 'CAS',
        'Catanduanes' => 'CAT',
        'Masbate' => 'MAS',
        'Sorsogon' => 'SOR',
    ];

    public function run(): void
    {
        if (DB::table('call_offs')->where('call_off_number', 'like', self::DEMO_PREFIX.'%')->exists()) {
            $this->command?->warn('Demo distribution workflow already exists; DistributionWorkflowSeeder skipped.');

            return;
        }

        $references = $this->references();

        DB::transaction(function () use ($references): void {
            // PO-2026-0001 deliberately contains TWO Call-Offs. Both Call-Offs
            // create THREE Delivery Receipts per province to exercise the
            // hierarchical PPE Distribution Summary introduced in v7.
            $this->seedApprovedWorkflow(
                purchaseOrderNumber: 'PO-2026-0001',
                callOffCode: '0001-A',
                distributionDate: Carbon::create(2026, 2, 5, 9),
                allocationScale: 1.00,
                receiptPercentages: [40, 35, 25],
                provinceStatus: 'Received',
                batchStatus: 'Completed',
                callOffStatus: 'Completed',
                projectPercentages: [45, 30, 0],
                references: $references
            );

            $this->seedApprovedWorkflow(
                purchaseOrderNumber: 'PO-2026-0001',
                callOffCode: '0001-B',
                distributionDate: Carbon::create(2026, 3, 10, 9),
                allocationScale: 0.85,
                receiptPercentages: [35, 25, 15], // 75% received; 25% still to receive.
                provinceStatus: 'Partially Received',
                batchStatus: 'Partially Received',
                callOffStatus: 'Approved',
                projectPercentages: [35, 20, 0],
                references: $references
            );

            // PO-2026-0002 contains another active Call-Off with three DRs so
            // filters have records from a second PO/supplier.
            $this->seedApprovedWorkflow(
                purchaseOrderNumber: 'PO-2026-0002',
                callOffCode: '0002-A',
                distributionDate: Carbon::create(2026, 5, 12, 9),
                allocationScale: 0.70,
                receiptPercentages: [30, 25, 20], // 75% received.
                provinceStatus: 'Partially Received',
                batchStatus: 'Partially Received',
                callOffStatus: 'Approved',
                projectPercentages: [30, 20, 0],
                references: $references
            );

            // Pending Call-Off is intentionally NOT exposed as a provincial
            // allocation. TSSD/Supply can test Pending status without creating
            // a Pending record in Provincial Office inventory tables.
            $this->seedPendingCallOff(
                purchaseOrderNumber: 'PO-2026-0002',
                callOffCode: '0002-B',
                distributionDate: Carbon::create(2026, 6, 18, 9),
                references: $references
            );

            $this->syncSupplyInventory($references['items']);
        });

        $this->command?->newLine();
        $this->command?->info('Deterministic PPE workflow demo data seeded successfully.');
        $this->command?->table(
            ['Seeded scenario', 'Result'],
            [
                ['PO-2026-0001', '2 Call-Offs'],
                ['CO-2026-DEMO-0001-A', '6 provinces × 3 DRs, fully received'],
                ['CO-2026-DEMO-0001-B', '6 provinces × 3 DRs, partially received'],
                ['PO-2026-0002', '1 active partial Call-Off + 1 Pending Call-Off'],
                ['PO-2026-0003', 'Undistributed Supply stock'],
                ['Projects', 'Linked directly to specific Delivery Receipts'],
                ['Provincial stock', 'Built from DR IN movements minus project OUT movements'],
            ]
        );
    }

    /**
     * @return array{items:Collection<int,object>,provinces:Collection<int,object>,tssd_user_id:int,supply_user_id:int,provincial_users:array<int,int>}
     */
    private function references(): array
    {
        $items = DB::table('items')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $provinces = DB::table('provinces')
            ->whereIn('name', array_keys(self::PROVINCE_BASE))
            ->get()
            ->sortBy(fn (object $province): int => array_search($province->name, array_keys(self::PROVINCE_BASE), true))
            ->values();

        $tssdUserId = DB::table('users')->where('username', 'tssd')->value('id');
        $supplyUserId = DB::table('users')->where('username', 'supply')->value('id');

        if ($items->isEmpty() || $provinces->count() !== 6 || ! $tssdUserId || ! $supplyUserId) {
            throw new RuntimeException('Required items, provinces, Supply user, or TSSD user are missing.');
        }

        $provincialUsers = DB::table('users')
            ->whereIn('province_id', $provinces->pluck('id'))
            ->whereNotNull('province_id')
            ->pluck('id', 'province_id')
            ->mapWithKeys(fn ($id, $provinceId): array => [(int) $provinceId => (int) $id])
            ->all();

        if (count($provincialUsers) !== 6) {
            throw new RuntimeException('All six Provincial Office users must exist before workflow seeding.');
        }

        return [
            'items' => $items,
            'provinces' => $provinces,
            'tssd_user_id' => (int) $tssdUserId,
            'supply_user_id' => (int) $supplyUserId,
            'provincial_users' => $provincialUsers,
        ];
    }

    /**
     * @param array<int,int> $receiptPercentages
     * @param array<int,int> $projectPercentages
     * @param array<string,mixed> $references
     */
    private function seedApprovedWorkflow(
        string $purchaseOrderNumber,
        string $callOffCode,
        Carbon $distributionDate,
        float $allocationScale,
        array $receiptPercentages,
        string $provinceStatus,
        string $batchStatus,
        string $callOffStatus,
        array $projectPercentages,
        array $references
    ): void {
        $purchaseOrder = DB::table('purchase_orders')
            ->where('po_number', $purchaseOrderNumber)
            ->first();

        if (! $purchaseOrder) {
            throw new RuntimeException("Missing seeded Purchase Order: {$purchaseOrderNumber}");
        }

        $batchId = $this->createBatch(
            purchaseOrderId: (int) $purchaseOrder->id,
            tssdUserId: $references['tssd_user_id'],
            distributionDate: $distributionDate,
            status: $batchStatus,
            callOffCode: $callOffCode
        );

        $callOffId = $this->createCallOff(
            purchaseOrderId: (int) $purchaseOrder->id,
            batchId: $batchId,
            tssdUserId: $references['tssd_user_id'],
            supplyUserId: $references['supply_user_id'],
            distributionDate: $distributionDate,
            status: $callOffStatus,
            callOffCode: $callOffCode
        );

        foreach ($references['provinces'] as $provinceIndex => $province) {
            $base = self::PROVINCE_BASE[$province->name];
            $allocation = max(1, (int) round($base * $allocationScale));
            $provinceUserId = $references['provincial_users'][(int) $province->id];

            $provinceDistributionId = $this->createProvinceDistribution(
                batchId: $batchId,
                province: $province,
                items: $references['items'],
                quantityPerItem: $allocation,
                distributionDate: $distributionDate,
                status: $provinceStatus
            );

            $receiptQuantities = $this->percentageParts($allocation, $receiptPercentages);
            $receiptIds = [];

            foreach ($receiptQuantities as $receiptIndex => $receivedQuantity) {
                if ($receivedQuantity <= 0) {
                    continue;
                }

                $receiptDate = $distributionDate
                    ->copy()
                    ->addDays(5 + ($receiptIndex * 4) + ((int) $provinceIndex % 2));

                $receiptIds[] = $this->createDeliveryReceipt(
                    callOffCode: $callOffCode,
                    receiptSequence: $receiptIndex + 1,
                    province: $province,
                    provinceDistributionId: $provinceDistributionId,
                    purchaseOrderId: (int) $purchaseOrder->id,
                    provincialUserId: $provinceUserId,
                    items: $references['items'],
                    allocationPerItem: $allocation,
                    receivedPerItem: $receivedQuantity,
                    receiptDate: $receiptDate,
                    tssdUserId: $references['tssd_user_id']
                );
            }

            foreach ($receiptIds as $receiptIndex => $receiptId) {
                $percentage = (int) ($projectPercentages[$receiptIndex] ?? 0);

                if ($percentage <= 0) {
                    continue;
                }

                $receiptItemQuantity = (int) DB::table('delivery_receipt_items')
                    ->where('delivery_receipt_id', $receiptId)
                    ->min('received_quantity');

                $projectQuantity = (int) floor($receiptItemQuantity * ($percentage / 100));

                if ($projectQuantity <= 0) {
                    continue;
                }

                $this->createProjectDesignation(
                    callOffCode: $callOffCode,
                    projectSequence: $receiptIndex + 1,
                    province: $province,
                    provinceDistributionId: $provinceDistributionId,
                    deliveryReceiptId: $receiptId,
                    provincialUserId: $provinceUserId,
                    items: $references['items'],
                    quantityPerItem: $projectQuantity,
                    projectDate: $distributionDate->copy()->addDays(22 + ($receiptIndex * 4))
                );
            }
        }

        $allocationValue = $this->allocationValueForBatch($batchId, (int) $purchaseOrder->id);

        DB::table('tssd_distribution_batches')->where('id', $batchId)->update([
            'call_off_letter_total_amount' => $allocationValue,
            'updated_at' => $distributionDate->copy()->addDays(2),
        ]);

        DB::table('call_offs')->where('id', $callOffId)->update([
            'print_total_amount' => $allocationValue,
            'updated_at' => $distributionDate->copy()->addDays(2),
        ]);
    }

    /**
     * Pending request is kept at the TSSD/Supply workflow level only.
     *
     * @param array<string,mixed> $references
     */
    private function seedPendingCallOff(
        string $purchaseOrderNumber,
        string $callOffCode,
        Carbon $distributionDate,
        array $references
    ): void {
        $purchaseOrder = DB::table('purchase_orders')
            ->where('po_number', $purchaseOrderNumber)
            ->first();

        if (! $purchaseOrder) {
            throw new RuntimeException("Missing seeded Purchase Order: {$purchaseOrderNumber}");
        }

        $batchId = $this->createBatch(
            purchaseOrderId: (int) $purchaseOrder->id,
            tssdUserId: $references['tssd_user_id'],
            distributionDate: $distributionDate,
            status: 'Pending Approval',
            callOffCode: $callOffCode
        );

        DB::table('call_offs')->insert([
            'tssd_distribution_batch_id' => $batchId,
            'purchase_order_id' => $purchaseOrder->id,
            'call_off_number' => self::DEMO_PREFIX.'-'.$callOffCode,
            'nefa_title' => 'TUPAD PPE Requirements CY 2026',
            'print_call_off_label' => 'CALL-OFF REQUEST',
            'print_distribution_batch' => $callOffCode,
            'print_total_amount' => null,
            'print_margin_top' => 9,
            'print_margin_right' => 11,
            'print_margin_bottom' => 28,
            'print_margin_left' => 11,
            'call_off_date' => null,
            'assigned_by' => $references['tssd_user_id'],
            'assigned_at' => $distributionDate,
            'approved_by' => null,
            'approved_at' => null,
            'approval_document' => null,
            'remarks' => 'Demo Pending Call-Off request visible to TSSD and Supply Unit.',
            'status' => 'Pending',
            'created_at' => $distributionDate,
            'updated_at' => $distributionDate,
        ]);
    }

    private function createBatch(
        int $purchaseOrderId,
        int $tssdUserId,
        Carbon $distributionDate,
        string $status,
        string $callOffCode
    ): int {
        return (int) DB::table('tssd_distribution_batches')->insertGetId([
            'purchase_order_id' => $purchaseOrderId,
            'created_by' => $tssdUserId,
            'distribution_date' => $distributionDate->format('Y-m-d'),
            'status' => $status,
            'remarks' => "Seeded PPE distribution batch {$callOffCode}.",
            'call_off_letter_nefa_title' => 'TUPAD PPE Requirements CY 2026',
            'call_off_letter_total_amount' => null,
            'call_off_letter_margin_top' => 9,
            'call_off_letter_margin_right' => 11,
            'call_off_letter_margin_bottom' => 28,
            'call_off_letter_margin_left' => 11,
            'call_off_letter_submitted_at' => $distributionDate,
            'created_at' => $distributionDate,
            'updated_at' => $distributionDate,
        ]);
    }

    private function createCallOff(
        int $purchaseOrderId,
        int $batchId,
        int $tssdUserId,
        int $supplyUserId,
        Carbon $distributionDate,
        string $status,
        string $callOffCode
    ): int {
        $approvedAt = $distributionDate->copy()->addDay()->setTime(10, 0);

        return (int) DB::table('call_offs')->insertGetId([
            'tssd_distribution_batch_id' => $batchId,
            'purchase_order_id' => $purchaseOrderId,
            'call_off_number' => self::DEMO_PREFIX.'-'.$callOffCode,
            'nefa_title' => 'TUPAD PPE Requirements CY 2026',
            'print_call_off_label' => 'CALL-OFF',
            'print_distribution_batch' => $callOffCode,
            'print_total_amount' => null,
            'print_margin_top' => 9,
            'print_margin_right' => 11,
            'print_margin_bottom' => 28,
            'print_margin_left' => 11,
            'call_off_date' => $approvedAt->format('Y-m-d'),
            'assigned_by' => $tssdUserId,
            'assigned_at' => $distributionDate,
            'approved_by' => $supplyUserId,
            'approved_at' => $approvedAt,
            'approval_document' => null,
            'remarks' => "Seeded approved Call-Off {$callOffCode}.",
            'status' => $status,
            'created_at' => $distributionDate,
            'updated_at' => $approvedAt,
        ]);
    }

    private function createProvinceDistribution(
        int $batchId,
        object $province,
        Collection $items,
        int $quantityPerItem,
        Carbon $distributionDate,
        string $status
    ): int {
        $scheduledDate = $distributionDate->copy()->addDays(5);

        $distributionId = (int) DB::table('province_distributions')->insertGetId([
            'tssd_distribution_batch_id' => $batchId,
            'province_id' => $province->id,
            'scheduled_delivery_date' => $scheduledDate->format('Y-m-d'),
            'place_of_delivery' => $province->delivery_address ?: $province->office_name ?: $province->name,
            'status' => $status,
            'received_at' => $status === 'Received' ? $distributionDate->copy()->addDays(14) : null,
            'remarks' => 'Seeded provincial PPE allocation.',
            'created_at' => $distributionDate,
            'updated_at' => $distributionDate,
        ]);

        foreach ($items as $item) {
            DB::table('province_distribution_items')->insert([
                'province_distribution_id' => $distributionId,
                'item_id' => $item->id,
                'quantity' => $quantityPerItem,
                'created_at' => $distributionDate,
                'updated_at' => $distributionDate,
            ]);
        }

        return $distributionId;
    }

    private function createDeliveryReceipt(
        string $callOffCode,
        int $receiptSequence,
        object $province,
        int $provinceDistributionId,
        int $purchaseOrderId,
        int $provincialUserId,
        Collection $items,
        int $allocationPerItem,
        int $receivedPerItem,
        Carbon $receiptDate,
        int $tssdUserId
    ): int {
        $provinceCode = self::PROVINCE_CODE[$province->name];
        $drNumber = sprintf('DR-2026-%s-%s-%02d', $callOffCode, $provinceCode, $receiptSequence);
        $receiverName = DB::table('users')->where('id', $provincialUserId)->value('name') ?: $province->office_name;

        $receiptId = (int) DB::table('delivery_receipts')->insertGetId([
            'province_distribution_id' => $provinceDistributionId,
            'purchase_order_id' => $purchaseOrderId,
            'province_id' => $province->id,
            'received_by_user_id' => $provincialUserId,
            'dr_number' => $drNumber,
            'delivery_date' => $receiptDate->format('Y-m-d'),
            'document' => null,
            'received_by' => $receiverName,
            'physical_receiver_name' => $receiverName,
            'remarks' => "Seeded Delivery Receipt {$receiptSequence} for {$province->name}.",
            'status' => 'Received',
            'submitted_at' => $receiptDate->copy()->addHours(2),
            'created_at' => $receiptDate,
            'updated_at' => $receiptDate,
        ]);

        $allocationItems = DB::table('province_distribution_items')
            ->where('province_distribution_id', $provinceDistributionId)
            ->get()
            ->keyBy('item_id');

        foreach ($items as $item) {
            $allocationItem = $allocationItems->get($item->id);
            $callOffBefore = $this->callOffAvailableBalance($provinceDistributionId, (int) $item->id);
            $pooledBefore = $this->provinceInventoryQuantity((int) $province->id, (int) $item->id);
            $callOffAfter = $callOffBefore + $receivedPerItem;
            $pooledAfter = $pooledBefore + $receivedPerItem;

            DB::table('delivery_receipt_items')->insert([
                'delivery_receipt_id' => $receiptId,
                'province_distribution_item_id' => $allocationItem?->id,
                'item_id' => $item->id,
                'quantity' => $receivedPerItem,
                'assigned_quantity' => $allocationPerItem,
                'received_quantity' => $receivedPerItem,
                'created_at' => $receiptDate,
                'updated_at' => $receiptDate,
            ]);

            DB::table('provincial_inventories')->updateOrInsert(
                ['province_id' => $province->id, 'item_id' => $item->id],
                ['quantity' => $pooledAfter, 'created_at' => $receiptDate, 'updated_at' => $receiptDate]
            );

            DB::table('inventory_movements')->insert([
                'province_id' => $province->id,
                'item_id' => $item->id,
                'created_by' => $provincialUserId,
                'province_distribution_id' => $provinceDistributionId,
                'delivery_receipt_id' => $receiptId,
                'supply_designation_id' => null,
                'movement_type' => 'IN',
                'quantity' => $receivedPerItem,
                'balance_before' => $pooledBefore,
                'balance_after' => $pooledAfter,
                'call_off_balance_before' => $callOffBefore,
                'call_off_balance_after' => $callOffAfter,
                'movement_date' => $receiptDate->format('Y-m-d'),
                'reference_number' => $drNumber,
                'description' => 'PPE received through seeded Delivery Receipt.',
                'remarks' => 'Generated by DistributionWorkflowSeeder.',
                'created_at' => $receiptDate,
                'updated_at' => $receiptDate,
            ]);
        }

        $callOffId = DB::table('call_offs')
            ->join('tssd_distribution_batches', 'tssd_distribution_batches.id', '=', 'call_offs.tssd_distribution_batch_id')
            ->where('tssd_distribution_batches.id', DB::table('province_distributions')->where('id', $provinceDistributionId)->value('tssd_distribution_batch_id'))
            ->value('call_offs.id');

        DB::table('workflow_notifications')->insert([
            'recipient_user_id' => $tssdUserId,
            'province_id' => $province->id,
            'call_off_id' => $callOffId,
            'delivery_receipt_id' => $receiptId,
            'type' => 'delivery_received',
            'title' => 'Delivery Receipt received',
            'message' => "{$province->name} submitted {$drNumber}.",
            'reference_type' => 'delivery_receipt',
            'reference_id' => $receiptId,
            'status' => $receiptSequence === 1 ? 'Read' : 'Unread',
            'read_at' => $receiptSequence === 1 ? $receiptDate->copy()->addDay() : null,
            'resolved_at' => null,
            'created_at' => $receiptDate,
            'updated_at' => $receiptDate,
        ]);

        return $receiptId;
    }

    private function createProjectDesignation(
        string $callOffCode,
        int $projectSequence,
        object $province,
        int $provinceDistributionId,
        int $deliveryReceiptId,
        int $provincialUserId,
        Collection $items,
        int $quantityPerItem,
        Carbon $projectDate
    ): void {
        $provinceCode = self::PROVINCE_CODE[$province->name];
        $projectCode = sprintf('TUPAD-%s-%s-P%02d', $callOffCode, $provinceCode, $projectSequence);
        $designationNumber = sprintf('DES-2026-%s-%s-%02d', $callOffCode, $provinceCode, $projectSequence);

        $designationId = (int) DB::table('supply_designations')->insertGetId([
            'delivery_receipt_id' => $deliveryReceiptId,
            'province_distribution_id' => $provinceDistributionId,
            'province_id' => $province->id,
            'created_by' => $provincialUserId,
            'designation_number' => $designationNumber,
            'designation_date' => $projectDate->format('Y-m-d'),
            'project_name' => "TUPAD Project {$projectCode}",
            'project_code' => $projectCode,
            'project_title' => "Community Employment Project {$projectSequence}",
            'location' => "{$province->name} Project Site {$projectSequence}",
            'number_of_days' => 10 + $projectSequence,
            'number_of_beneficiaries' => 40 + ($projectSequence * 10),
            'are_document' => null,
            'status' => 'Completed',
            'submitted_at' => $projectDate->copy()->addHours(2),
            'remarks' => 'Seeded project PPE distribution linked to a specific Delivery Receipt.',
            'created_at' => $projectDate,
            'updated_at' => $projectDate,
        ]);

        foreach ($items as $item) {
            $pooledBefore = $this->provinceInventoryQuantity((int) $province->id, (int) $item->id);
            $callOffBefore = $this->callOffAvailableBalance($provinceDistributionId, (int) $item->id);
            $receiptRemaining = $this->receiptAvailableBalance($deliveryReceiptId, (int) $item->id);
            $quantity = min($quantityPerItem, $pooledBefore, $callOffBefore, $receiptRemaining);

            if ($quantity <= 0) {
                continue;
            }

            DB::table('supply_designation_items')->insert([
                'supply_designation_id' => $designationId,
                'item_id' => $item->id,
                'quantity' => $quantity,
                'created_at' => $projectDate,
                'updated_at' => $projectDate,
            ]);

            $pooledAfter = $pooledBefore - $quantity;
            $callOffAfter = $callOffBefore - $quantity;

            DB::table('provincial_inventories')
                ->where('province_id', $province->id)
                ->where('item_id', $item->id)
                ->update(['quantity' => $pooledAfter, 'updated_at' => $projectDate]);

            DB::table('inventory_movements')->insert([
                'province_id' => $province->id,
                'item_id' => $item->id,
                'created_by' => $provincialUserId,
                'province_distribution_id' => $provinceDistributionId,
                'delivery_receipt_id' => $deliveryReceiptId,
                'supply_designation_id' => $designationId,
                'movement_type' => 'OUT',
                'quantity' => $quantity,
                'balance_before' => $pooledBefore,
                'balance_after' => $pooledAfter,
                'call_off_balance_before' => $callOffBefore,
                'call_off_balance_after' => $callOffAfter,
                'movement_date' => $projectDate->format('Y-m-d'),
                'reference_number' => $projectCode,
                'description' => 'PPE distributed to seeded TUPAD project.',
                'remarks' => 'Generated by DistributionWorkflowSeeder.',
                'created_at' => $projectDate,
                'updated_at' => $projectDate,
            ]);
        }
    }

    /**
     * @param array<int,int> $percentages
     * @return array<int,int>
     */
    private function percentageParts(int $allocation, array $percentages): array
    {
        $parts = [];
        $assigned = 0;
        $totalPercentage = array_sum($percentages);
        $lastIndex = array_key_last($percentages);

        foreach ($percentages as $index => $percentage) {
            if ($index === $lastIndex && $totalPercentage >= 100) {
                // A complete delivery must add back to the exact allocation,
                // even when earlier percentage calculations were rounded down.
                $quantity = max(0, $allocation - $assigned);
            } else {
                $quantity = max(0, (int) floor($allocation * ($percentage / 100)));
            }

            $parts[] = $quantity;
            $assigned += $quantity;
        }

        return $parts;
    }

    private function provinceInventoryQuantity(int $provinceId, int $itemId): int
    {
        return (int) (DB::table('provincial_inventories')
            ->where('province_id', $provinceId)
            ->where('item_id', $itemId)
            ->value('quantity') ?? 0);
    }

    private function callOffAvailableBalance(int $provinceDistributionId, int $itemId): int
    {
        $received = (int) DB::table('delivery_receipt_items')
            ->join('delivery_receipts', 'delivery_receipts.id', '=', 'delivery_receipt_items.delivery_receipt_id')
            ->where('delivery_receipts.province_distribution_id', $provinceDistributionId)
            ->where('delivery_receipts.status', 'Received')
            ->where('delivery_receipt_items.item_id', $itemId)
            ->sum('delivery_receipt_items.received_quantity');

        $distributed = (int) DB::table('supply_designation_items')
            ->join('supply_designations', 'supply_designations.id', '=', 'supply_designation_items.supply_designation_id')
            ->where('supply_designations.province_distribution_id', $provinceDistributionId)
            ->where('supply_designations.status', 'Completed')
            ->where('supply_designation_items.item_id', $itemId)
            ->sum('supply_designation_items.quantity');

        return max(0, $received - $distributed);
    }

    private function receiptAvailableBalance(int $deliveryReceiptId, int $itemId): int
    {
        $received = (int) DB::table('delivery_receipt_items')
            ->where('delivery_receipt_id', $deliveryReceiptId)
            ->where('item_id', $itemId)
            ->sum('received_quantity');

        $distributed = (int) DB::table('supply_designation_items')
            ->join('supply_designations', 'supply_designations.id', '=', 'supply_designation_items.supply_designation_id')
            ->where('supply_designations.delivery_receipt_id', $deliveryReceiptId)
            ->where('supply_designations.status', 'Completed')
            ->where('supply_designation_items.item_id', $itemId)
            ->sum('supply_designation_items.quantity');

        return max(0, $received - $distributed);
    }

    private function allocationValueForBatch(int $batchId, int $purchaseOrderId): float
    {
        $unitCostByItem = DB::table('purchase_order_items')
            ->where('purchase_order_id', $purchaseOrderId)
            ->pluck('unit_cost', 'item_id');

        $rows = DB::table('province_distribution_items')
            ->join('province_distributions', 'province_distributions.id', '=', 'province_distribution_items.province_distribution_id')
            ->where('province_distributions.tssd_distribution_batch_id', $batchId)
            ->select('province_distribution_items.item_id', 'province_distribution_items.quantity')
            ->get();

        return (float) $rows->sum(function (object $row) use ($unitCostByItem): float {
            return ((int) $row->quantity) * ((float) ($unitCostByItem[$row->item_id] ?? 0));
        });
    }

    /**
     * Supply's central inventory follows the application's rebuild rule:
     * only Purchase Orders with no non-cancelled Provincial Distribution are
     * counted as still available in Supply.
     */
    private function syncSupplyInventory(Collection $items): void
    {
        foreach ($items as $item) {
            $quantity = (int) DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->where('purchase_order_items.item_id', $item->id)
                ->whereNotExists(function ($query): void {
                    $query
                        ->selectRaw('1')
                        ->from('tssd_distribution_batches')
                        ->join('province_distributions', 'province_distributions.tssd_distribution_batch_id', '=', 'tssd_distribution_batches.id')
                        ->whereColumn('tssd_distribution_batches.purchase_order_id', 'purchase_orders.id')
                        ->where('province_distributions.status', '!=', 'Cancelled');
                })
                ->sum('purchase_order_items.quantity');

            DB::table('inventory')->updateOrInsert(
                ['item_id' => $item->id],
                ['quantity' => $quantity, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
