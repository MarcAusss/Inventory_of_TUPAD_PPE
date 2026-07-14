<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DistributionWorkflowSeeder extends Seeder
{
    private const PREFIX = 'DEMO-2026';

    /**
     * Seven PPE variants are expected:
     *
     * 1 - Long Sleeve Medium
     * 2 - Long Sleeve Large
     * 3 - Bucket Hat
     * 4 - Rubber Boots US9
     * 5 - Rubber Boots US10
     * 6 - Hand Gloves
     * 7 - Mask
     */
    private const PO_QUANTITY_PER_ITEM = 5000;

    /**
     * Provincial allocation for every PPE item.
     */
    private const ALLOCATION_PER_ITEM = 100;

    public function run(): void
    {
        $alreadyExists = DB::table('purchase_orders')
            ->where(
                'po_number',
                'like',
                self::PREFIX.'%'
            )
            ->exists();

        if ($alreadyExists) {
            $this->command?->warn(
                'The 2026 distribution workflow demo records already exist.'
            );

            $this->command?->line(
                'Seeder stopped to prevent duplicate inventory movements.'
            );

            return;
        }

        $references = $this->resolveReferences();

        DB::transaction(
            function () use ($references): void {
                foreach (
                    $references['supply_users']
                    as $supplyUser
                ) {
                    $this->seedForSupplyAccount(
                        supplyUserId: (int) $supplyUser->id,
                        supplySequence: (int) $supplyUser->id,
                        references: $references
                    );
                }
            }
        );

        $this->command?->newLine();

        $this->command?->info(
            'Distribution workflow records were seeded successfully.'
        );

        $this->command?->table(
            [
                'Requirement',
                'Created',
            ],
            [
                [
                    'Purchase Orders per Supply account',
                    '9',
                ],
                [
                    'Call-Offs per Provincial Office',
                    '9',
                ],
                [
                    'Covered months',
                    'February to July 2026',
                ],
                [
                    'Provincial Offices',
                    '6',
                ],
                [
                    'Partial deliveries',
                    'Included',
                ],
                [
                    'Pending allocations',
                    'Included',
                ],
                [
                    'Completed projects',
                    'Included',
                ],
                [
                    'Multiple DRs per Call-Off',
                    'Included',
                ],
            ]
        );
    }

    /**
     * Create nine Purchase Orders and Call-Off workflows for one
     * Supply Unit account.
     *
     * Every Call-Off contains all six Provincial Offices. Therefore,
     * every Provincial Office receives nine Call-Off allocations.
     *
     * @param array<string, mixed> $references
     */
    private function seedForSupplyAccount(
        int $supplyUserId,
        int $supplySequence,
        array $references
    ): void {
        $scenarios = [
            [
                'sequence' => 1,
                'date' => Carbon::create(2026, 2, 5),
                'batch_status' => 'Completed',
                'call_off_status' => 'Completed',
                'province_status' => 'Received',
                'delivery_mode' => 'complete',
                'project_quantity' => 25,
                'po_status' => 'Completed',
            ],

            [
                'sequence' => 2,
                'date' => Carbon::create(2026, 2, 20),
                'batch_status' => 'Completed',
                'call_off_status' => 'Completed',
                'province_status' => 'Received',
                'delivery_mode' => 'complete',
                'project_quantity' => 0,
                'po_status' => 'Completed',
            ],

            [
                'sequence' => 3,
                'date' => Carbon::create(2026, 3, 12),
                'batch_status' => 'Completed',
                'call_off_status' => 'Completed',
                'province_status' => 'Received',
                'delivery_mode' => 'complete',
                'project_quantity' => 30,
                'po_status' => 'Completed',
            ],

            [
                'sequence' => 4,
                'date' => Carbon::create(2026, 4, 8),
                'batch_status' => 'Partially Received',
                'call_off_status' => 'Approved',
                'province_status' => 'Partially Received',
                'delivery_mode' => 'partial',
                'project_quantity' => 0,
                'po_status' => 'Distributed',
            ],

            [
                'sequence' => 5,
                'date' => Carbon::create(2026, 5, 15),
                'batch_status' => 'Approved',
                'call_off_status' => 'Approved',
                'province_status' => 'For Delivery',
                'delivery_mode' => 'none',
                'project_quantity' => 0,
                'po_status' => 'Distributed',
            ],

            [
                'sequence' => 6,
                'date' => Carbon::create(2026, 6, 3),
                'batch_status' => 'Pending Approval',
                'call_off_status' => 'Pending',
                'province_status' => 'Pending',
                'delivery_mode' => 'none',
                'project_quantity' => 0,
                'po_status' => 'Distributed',
            ],

            [
                'sequence' => 7,
                'date' => Carbon::create(2026, 6, 21),
                'batch_status' => 'Call-Off Assigned',
                'call_off_status' => 'Pending',
                'province_status' => 'Pending',
                'delivery_mode' => 'none',
                'project_quantity' => 0,
                'po_status' => 'Distributed',
            ],

            [
                'sequence' => 8,
                'date' => Carbon::create(2026, 7, 4),
                'batch_status' => 'Completed',
                'call_off_status' => 'Completed',
                'province_status' => 'Received',
                'delivery_mode' => 'complete',
                'project_quantity' => 20,
                'po_status' => 'Completed',
            ],

            [
                'sequence' => 9,
                'date' => Carbon::create(2026, 7, 10),
                'batch_status' => 'Completed',
                'call_off_status' => 'Completed',
                'province_status' => 'Received',
                'delivery_mode' => 'multiple',
                'project_quantity' => 15,
                'po_status' => 'Completed',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $this->createScenario(
                supplyUserId: $supplyUserId,
                supplySequence: $supplySequence,
                scenario: $scenario,
                references: $references
            );
        }
    }

    /**
     * @param array<string, mixed> $scenario
     * @param array<string, mixed> $references
     */
    private function createScenario(
        int $supplyUserId,
        int $supplySequence,
        array $scenario,
        array $references
    ): void {
        $sequence = (int) $scenario['sequence'];

        /** @var Carbon $scenarioDate */
        $scenarioDate = $scenario['date'];

        $supplierId = $references['supplier_ids'][
            ($sequence - 1)
            % count($references['supplier_ids'])
        ];

        $reference = sprintf(
            'S%02d-%02d',
            $supplySequence,
            $sequence
        );

        $purchaseOrderId = $this->createPurchaseOrder(
            reference: $reference,
            supplierId: $supplierId,
            supplyUserId: $supplyUserId,
            items: $references['items'],
            date: $scenarioDate->copy()->subDays(10),
            status: $scenario['po_status']
        );

        $batchId = $this->createDistributionBatch(
            purchaseOrderId: $purchaseOrderId,
            tssdUserId: $references['tssd_user_id'],
            date: $scenarioDate,
            status: $scenario['batch_status'],
            reference: $reference
        );

        $callOffId = $this->createCallOff(
            reference: $reference,
            purchaseOrderId: $purchaseOrderId,
            batchId: $batchId,
            tssdUserId: $references['tssd_user_id'],
            supplyUserId: $supplyUserId,
            date: $scenarioDate,
            status: $scenario['call_off_status']
        );

        foreach (
            $references['provinces'] as $province
        ) {
            $provinceId = (int) $province->id;

            $provinceUserId = (int) (
                $references['provincial_users'][
                    $provinceId
                ]
            );

            $provinceDistributionId =
                $this->createProvinceDistribution(
                    batchId: $batchId,
                    province: $province,
                    items: $references['items'],
                    date: $scenarioDate,
                    status: $scenario['province_status']
                );

            $deliveryReceiptIds = [];

            if ($scenario['delivery_mode'] === 'complete') {
                $deliveryReceiptIds[] =
                    $this->createDeliveryReceipt(
                        reference: $reference.'-P'.$provinceId,
                        provinceDistributionId:
                            $provinceDistributionId,
                        purchaseOrderId: $purchaseOrderId,
                        provinceId: $provinceId,
                        receivedByUserId: $provinceUserId,
                        items: $references['items'],
                        assignedQuantity:
                            self::ALLOCATION_PER_ITEM,
                        receivedQuantity:
                            self::ALLOCATION_PER_ITEM,
                        date: $scenarioDate
                            ->copy()
                            ->addDays(5)
                    );
            }

            if ($scenario['delivery_mode'] === 'partial') {
                $deliveryReceiptIds[] =
                    $this->createDeliveryReceipt(
                        reference: $reference.'-P'.$provinceId.'-A',
                        provinceDistributionId:
                            $provinceDistributionId,
                        purchaseOrderId: $purchaseOrderId,
                        provinceId: $provinceId,
                        receivedByUserId: $provinceUserId,
                        items: $references['items'],
                        assignedQuantity:
                            self::ALLOCATION_PER_ITEM,
                        receivedQuantity: 60,
                        date: $scenarioDate
                            ->copy()
                            ->addDays(5)
                    );
            }

            if ($scenario['delivery_mode'] === 'multiple') {
                $deliveryReceiptIds[] =
                    $this->createDeliveryReceipt(
                        reference: $reference.'-P'.$provinceId.'-A',
                        provinceDistributionId:
                            $provinceDistributionId,
                        purchaseOrderId: $purchaseOrderId,
                        provinceId: $provinceId,
                        receivedByUserId: $provinceUserId,
                        items: $references['items'],
                        assignedQuantity:
                            self::ALLOCATION_PER_ITEM,
                        receivedQuantity: 60,
                        date: $scenarioDate
                            ->copy()
                            ->addDays(3)
                    );

                $deliveryReceiptIds[] =
                    $this->createDeliveryReceipt(
                        reference: $reference.'-P'.$provinceId.'-B',
                        provinceDistributionId:
                            $provinceDistributionId,
                        purchaseOrderId: $purchaseOrderId,
                        provinceId: $provinceId,
                        receivedByUserId: $provinceUserId,
                        items: $references['items'],
                        assignedQuantity:
                            self::ALLOCATION_PER_ITEM,
                        receivedQuantity: 40,
                        date: $scenarioDate
                            ->copy()
                            ->addDays(7)
                    );
            }

            foreach ($deliveryReceiptIds as $receiptId) {
                $this->applyReceiptToInventory(
                    deliveryReceiptId: $receiptId
                );
            }

            $projectQuantity = (int) (
                $scenario['project_quantity']
                ?? 0
            );

            if (
                $projectQuantity > 0
                && $deliveryReceiptIds !== []
            ) {
                $this->createProjectDesignation(
                    reference: $reference.'-P'.$provinceId,
                    provinceDistributionId:
                        $provinceDistributionId,
                    provinceId: $provinceId,
                    createdByUserId: $provinceUserId,
                    items: $references['items'],
                    quantity: $projectQuantity,
                    date: $scenarioDate
                        ->copy()
                        ->addDays(12)
                );
            }

            $this->createWorkflowNotification(
                recipientUserId:
                    $references['tssd_user_id'],
                provinceId: $provinceId,
                callOffId: $callOffId,
                deliveryReceiptId:
                    $deliveryReceiptIds !== []
                        ? end($deliveryReceiptIds)
                        : null,
                reference: $reference,
                status: $scenario['province_status']
            );
        }

        if (
            $scenario['batch_status']
            === 'Pending Approval'
        ) {
            $this->createWorkflowNotification(
                recipientUserId: $supplyUserId,
                provinceId: null,
                callOffId: $callOffId,
                deliveryReceiptId: null,
                reference: $reference,
                status: 'Pending Approval'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveReferences(): array
    {
        $supplyRoleId = DB::table('roles')
            ->where('name', 'Supply Unit')
            ->value('id');

        $tssdRoleId = DB::table('roles')
            ->where('name', 'TSSD Unit')
            ->value('id');

        $provincialRoleId = DB::table('roles')
            ->where('name', 'Provincial Office')
            ->value('id');

        if (
            ! $supplyRoleId
            || ! $tssdRoleId
            || ! $provincialRoleId
        ) {
            throw new RuntimeException(
                'Required roles are missing. Run RoleSeeder first.'
            );
        }

        $supplyUsers = DB::table('users')
            ->where(
                'role_id',
                $supplyRoleId
            )
            ->orderBy('id')
            ->get();

        if ($supplyUsers->isEmpty()) {
            throw new RuntimeException(
                'No Supply Unit account was found. Run UserSeeder first.'
            );
        }

        $tssdUser = DB::table('users')
            ->where(
                'role_id',
                $tssdRoleId
            )
            ->orderBy('id')
            ->first();

        if (! $tssdUser) {
            throw new RuntimeException(
                'No TSSD Unit account was found. Run UserSeeder first.'
            );
        }

        $provinces = DB::table('provinces')
            ->orderBy('id')
            ->get();

        if ($provinces->count() < 6) {
            throw new RuntimeException(
                'Six Provincial Offices are required.'
            );
        }

        $provincialUsers = [];

        foreach ($provinces as $province) {
            $userId = DB::table('users')
                ->where(
                    'role_id',
                    $provincialRoleId
                )
                ->where(
                    'province_id',
                    $province->id
                )
                ->value('id');

            if (! $userId) {
                throw new RuntimeException(
                    "No Provincial Office user exists for {$province->name}."
                );
            }

            $provincialUsers[
                (int) $province->id
            ] = (int) $userId;
        }

        $items = DB::table('items')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($items->count() < 7) {
            throw new RuntimeException(
                'Seven active PPE variants are required.'
            );
        }

        $supplierIds = DB::table('suppliers')
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->values()
            ->all();

        if ($supplierIds === []) {
            throw new RuntimeException(
                'At least one active supplier is required.'
            );
        }

        return [
            'supply_users' => $supplyUsers,

            'tssd_user_id' => (int) $tssdUser->id,

            'provinces' => $provinces,

            'provincial_users' => $provincialUsers,

            'items' => $items,

            'supplier_ids' => $supplierIds,
        ];
    }

    private function createPurchaseOrder(
        string $reference,
        int $supplierId,
        int $supplyUserId,
        Collection $items,
        Carbon $date,
        string $status
    ): int {
        $purchaseOrderId = DB::table('purchase_orders')
            ->insertGetId([
                'supplier_id' => $supplierId,

                'created_by' => $supplyUserId,

                'po_number' =>
                    self::PREFIX.'-PO-'.$reference,

                'po_date' =>
                    $date->format('Y-m-d'),

                'nefa_number' =>
                    self::PREFIX.'-NEFA-'.$reference,

                'total_amount' => 0,

                'document' => null,

                'status' => $status,

                'remarks' =>
                    'Generated by DistributionWorkflowSeeder.',

                'created_at' => $date,

                'updated_at' => $date,
            ]);

        $totalAmount = 0;

        foreach ($items as $index => $item) {
            $unitCost = 150 + ($index * 25);

            $totalCost =
                self::PO_QUANTITY_PER_ITEM
                * $unitCost;

            DB::table('purchase_order_items')
                ->insert([
                    'purchase_order_id' =>
                        $purchaseOrderId,

                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        self::PO_QUANTITY_PER_ITEM,

                    'unit_cost' =>
                        $unitCost,

                    'total_cost' =>
                        $totalCost,

                    'size' =>
                        $item->label,

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);

            $totalAmount += $totalCost;
        }

        DB::table('purchase_orders')
            ->where(
                'id',
                $purchaseOrderId
            )
            ->update([
                'total_amount' => $totalAmount,

                'updated_at' => $date,
            ]);

        return $purchaseOrderId;
    }

    private function createDistributionBatch(
        int $purchaseOrderId,
        int $tssdUserId,
        Carbon $date,
        string $status,
        string $reference
    ): int {
        return DB::table('tssd_distribution_batches')
            ->insertGetId([
                'purchase_order_id' =>
                    $purchaseOrderId,

                'created_by' =>
                    $tssdUserId,

                'distribution_date' =>
                    $date->format('Y-m-d'),

                'status' =>
                    $status,

                'remarks' =>
                    "Seeded distribution batch {$reference}.",

                'created_at' =>
                    $date,

                'updated_at' =>
                    $date,
            ]);
    }

    private function createCallOff(
        string $reference,
        int $purchaseOrderId,
        int $batchId,
        int $tssdUserId,
        int $supplyUserId,
        Carbon $date,
        string $status
    ): int {
        $approved = in_array(
            $status,
            [
                'Approved',
                'Completed',
            ],
            true
        );

        return DB::table('call_offs')
            ->insertGetId([
                'tssd_distribution_batch_id' =>
                    $batchId,

                'purchase_order_id' =>
                    $purchaseOrderId,

                'call_off_number' =>
                    self::PREFIX.'-CO-'.$reference,

                'call_off_date' =>
                    $date->format('Y-m-d'),

                'assigned_by' =>
                    $tssdUserId,

                'assigned_at' =>
                    $date,

                'approved_by' =>
                    $approved
                        ? $supplyUserId
                        : null,

                'approved_at' =>
                    $approved
                        ? $date->copy()->addDay()
                        : null,

                'approval_document' =>
                    null,

                'remarks' =>
                    "Seeded Call-Off {$reference}.",

                'status' =>
                    $status,

                'created_at' =>
                    $date,

                'updated_at' =>
                    $date,
            ]);
    }

    private function createProvinceDistribution(
        int $batchId,
        object $province,
        Collection $items,
        Carbon $date,
        string $status
    ): int {
        $receivedAt = $status === 'Received'
            ? $date->copy()->addDays(7)
            : null;

        $provinceDistributionId =
            DB::table('province_distributions')
                ->insertGetId([
                    'tssd_distribution_batch_id' =>
                        $batchId,

                    'province_id' =>
                        $province->id,

                    'scheduled_delivery_date' =>
                        $date
                            ->copy()
                            ->addDays(5)
                            ->format('Y-m-d'),

                    'place_of_delivery' =>
                        $province->delivery_address
                        ?? $province->office_name
                        ?? $province->name,

                    'status' =>
                        $status,

                    'received_at' =>
                        $receivedAt,

                    'remarks' =>
                        'Generated provincial allocation.',

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);

        foreach ($items as $item) {
            DB::table('province_distribution_items')
                ->insert([
                    'province_distribution_id' =>
                        $provinceDistributionId,

                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        self::ALLOCATION_PER_ITEM,

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);
        }

        return $provinceDistributionId;
    }

    private function createDeliveryReceipt(
        string $reference,
        int $provinceDistributionId,
        int $purchaseOrderId,
        int $provinceId,
        int $receivedByUserId,
        Collection $items,
        int $assignedQuantity,
        int $receivedQuantity,
        Carbon $date
    ): int {
        $receiver = DB::table('users')
            ->where(
                'id',
                $receivedByUserId
            )
            ->value('name')
            ?? 'Provincial Office Receiver';

        $receiptId = DB::table('delivery_receipts')
            ->insertGetId([
                'province_distribution_id' =>
                    $provinceDistributionId,

                'purchase_order_id' =>
                    $purchaseOrderId,

                'province_id' =>
                    $provinceId,

                'received_by_user_id' =>
                    $receivedByUserId,

                'physical_receiver_name' =>
                    $receiver,

                'dr_number' =>
                    self::PREFIX.'-DR-'.$reference,

                'delivery_date' =>
                    $date->format('Y-m-d'),

                'document' =>
                    null,

                'received_by' =>
                    $receiver,

                'remarks' =>
                    'Generated Delivery Receipt.',

                'status' =>
                    'Received',

                'submitted_at' =>
                    $date,

                'created_at' =>
                    $date,

                'updated_at' =>
                    $date,
            ]);

        $allocationItems = DB::table(
            'province_distribution_items'
        )
            ->where(
                'province_distribution_id',
                $provinceDistributionId
            )
            ->get()
            ->keyBy('item_id');

        foreach ($items as $item) {
            $allocationItem = $allocationItems->get(
                $item->id
            );

            DB::table('delivery_receipt_items')
                ->insert([
                    'delivery_receipt_id' =>
                        $receiptId,

                    'province_distribution_item_id' =>
                        $allocationItem->id,

                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        $receivedQuantity,

                    'assigned_quantity' =>
                        $assignedQuantity,

                    'received_quantity' =>
                        $receivedQuantity,

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);
        }

        return $receiptId;
    }

    private function applyReceiptToInventory(
        int $deliveryReceiptId
    ): void {
        $receipt = DB::table('delivery_receipts')
            ->where(
                'id',
                $deliveryReceiptId
            )
            ->first();

        $receiptItems = DB::table(
            'delivery_receipt_items'
        )
            ->where(
                'delivery_receipt_id',
                $deliveryReceiptId
            )
            ->get();

        foreach ($receiptItems as $receiptItem) {
            $quantity =
                (int) $receiptItem->received_quantity;

            $inventory = DB::table(
                'provincial_inventories'
            )
                ->where(
                    'province_id',
                    $receipt->province_id
                )
                ->where(
                    'item_id',
                    $receiptItem->item_id
                )
                ->first();

            $pooledBefore = $inventory
                ? (int) $inventory->quantity
                : 0;

            $pooledAfter =
                $pooledBefore + $quantity;

            if ($inventory) {
                DB::table('provincial_inventories')
                    ->where(
                        'id',
                        $inventory->id
                    )
                    ->update([
                        'quantity' =>
                            $pooledAfter,

                        'updated_at' =>
                            $receipt->delivery_date,
                    ]);
            } else {
                DB::table('provincial_inventories')
                    ->insert([
                        'province_id' =>
                            $receipt->province_id,

                        'item_id' =>
                            $receiptItem->item_id,

                        'quantity' =>
                            $pooledAfter,

                        'created_at' =>
                            $receipt->delivery_date,

                        'updated_at' =>
                            $receipt->delivery_date,
                    ]);
            }

            $callOffBefore = (int) DB::table(
                'inventory_movements'
            )
                ->where(
                    'province_distribution_id',
                    $receipt->province_distribution_id
                )
                ->where(
                    'item_id',
                    $receiptItem->item_id
                )
                ->where(
                    'movement_type',
                    'IN'
                )
                ->sum('quantity');

            $callOffAfter =
                $callOffBefore + $quantity;

            DB::table('inventory_movements')
                ->insert([
                    'province_id' =>
                        $receipt->province_id,

                    'item_id' =>
                        $receiptItem->item_id,

                    'created_by' =>
                        $receipt->received_by_user_id,

                    'province_distribution_id' =>
                        $receipt->province_distribution_id,

                    'delivery_receipt_id' =>
                        $receipt->id,

                    'supply_designation_id' =>
                        null,

                    'movement_type' =>
                        'IN',

                    'quantity' =>
                        $quantity,

                    'balance_before' =>
                        $pooledBefore,

                    'balance_after' =>
                        $pooledAfter,

                    'call_off_balance_before' =>
                        $callOffBefore,

                    'call_off_balance_after' =>
                        $callOffAfter,

                    'movement_date' =>
                        $receipt->delivery_date,

                    'reference_number' =>
                        $receipt->dr_number,

                    'description' =>
                        'PPE received through seeded Delivery Receipt.',

                    'remarks' =>
                        'Generated by DistributionWorkflowSeeder.',

                    'created_at' =>
                        $receipt->delivery_date,

                    'updated_at' =>
                        $receipt->delivery_date,
                ]);
        }
    }

    private function createProjectDesignation(
        string $reference,
        int $provinceDistributionId,
        int $provinceId,
        int $createdByUserId,
        Collection $items,
        int $quantity,
        Carbon $date
    ): void {
        $designationNumber =
            self::PREFIX.'-DES-'.$reference;

        $projectCode =
            self::PREFIX.'-PRJ-'.$reference;

        $designationId = DB::table(
            'supply_designations'
        )
            ->insertGetId([
                'delivery_receipt_id' =>
                    null,

                'province_distribution_id' =>
                    $provinceDistributionId,

                'province_id' =>
                    $provinceId,

                'created_by' =>
                    $createdByUserId,

                'designation_number' =>
                    $designationNumber,

                'designation_date' =>
                    $date->format('Y-m-d'),

                /*
                 * Legacy required field.
                 */
                'project_name' =>
                    "TUPAD Demo Project {$reference}",

                'project_code' =>
                    $projectCode,

                'project_title' =>
                    "TUPAD PPE Project {$reference}",

                'location' =>
                    'Provincial Project Site',

                'number_of_days' =>
                    10,

                'number_of_beneficiaries' =>
                    50,

                'are_document' =>
                    null,

                'status' =>
                    'Completed',

                'submitted_at' =>
                    $date,

                'remarks' =>
                    'Generated project PPE designation.',

                'created_at' =>
                    $date,

                'updated_at' =>
                    $date,
            ]);

        foreach ($items as $item) {
            $inventory = DB::table(
                'provincial_inventories'
            )
                ->where(
                    'province_id',
                    $provinceId
                )
                ->where(
                    'item_id',
                    $item->id
                )
                ->first();

            if (
                ! $inventory
                || (int) $inventory->quantity
                    < $quantity
            ) {
                throw new RuntimeException(
                    "Insufficient seeded inventory for item #{$item->id}."
                );
            }

            $callOffReceived = (int) DB::table(
                'delivery_receipt_items'
            )
                ->join(
                    'delivery_receipts',
                    'delivery_receipts.id',
                    '=',
                    'delivery_receipt_items.delivery_receipt_id'
                )
                ->where(
                    'delivery_receipts.province_distribution_id',
                    $provinceDistributionId
                )
                ->where(
                    'delivery_receipt_items.item_id',
                    $item->id
                )
                ->sum(
                    'delivery_receipt_items.received_quantity'
                );

            $previouslyDistributed = (int) DB::table(
                'supply_designation_items'
            )
                ->join(
                    'supply_designations',
                    'supply_designations.id',
                    '=',
                    'supply_designation_items.supply_designation_id'
                )
                ->where(
                    'supply_designations.province_distribution_id',
                    $provinceDistributionId
                )
                ->where(
                    'supply_designation_items.item_id',
                    $item->id
                )
                ->where(
                    'supply_designations.status',
                    'Completed'
                )
                ->sum(
                    'supply_designation_items.quantity'
                );

            $callOffBefore = max(
                0,
                $callOffReceived
                    - $previouslyDistributed
            );

            $callOffAfter = max(
                0,
                $callOffBefore - $quantity
            );

            $pooledBefore =
                (int) $inventory->quantity;

            $pooledAfter =
                $pooledBefore - $quantity;

            DB::table('supply_designation_items')
                ->insert([
                    'supply_designation_id' =>
                        $designationId,

                    'item_id' =>
                        $item->id,

                    'quantity' =>
                        $quantity,

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);

            DB::table('provincial_inventories')
                ->where(
                    'id',
                    $inventory->id
                )
                ->update([
                    'quantity' =>
                        $pooledAfter,

                    'updated_at' =>
                        $date,
                ]);

            DB::table('inventory_movements')
                ->insert([
                    'province_id' =>
                        $provinceId,

                    'item_id' =>
                        $item->id,

                    'created_by' =>
                        $createdByUserId,

                    'province_distribution_id' =>
                        $provinceDistributionId,

                    'delivery_receipt_id' =>
                        null,

                    'supply_designation_id' =>
                        $designationId,

                    'movement_type' =>
                        'OUT',

                    'quantity' =>
                        $quantity,

                    'balance_before' =>
                        $pooledBefore,

                    'balance_after' =>
                        $pooledAfter,

                    'call_off_balance_before' =>
                        $callOffBefore,

                    'call_off_balance_after' =>
                        $callOffAfter,

                    'movement_date' =>
                        $date->format('Y-m-d'),

                    'reference_number' =>
                        $projectCode,

                    'description' =>
                        'PPE distributed to a seeded project.',

                    'remarks' =>
                        'Generated by DistributionWorkflowSeeder.',

                    'created_at' =>
                        $date,

                    'updated_at' =>
                        $date,
                ]);
        }
    }

    private function createWorkflowNotification(
        int $recipientUserId,
        ?int $provinceId,
        int $callOffId,
        ?int $deliveryReceiptId,
        string $reference,
        string $status
    ): void {
        $type = match ($status) {
            'Pending Approval' =>
                'call_off_pending_approval',

            'Partially Received' =>
                'partial_delivery_received',

            'Received' =>
                'delivery_completed',

            'For Delivery' =>
                'approved_for_delivery',

            default =>
                'distribution_workflow_update',
        };

        DB::table('workflow_notifications')
            ->insert([
                'recipient_user_id' =>
                    $recipientUserId,

                'province_id' =>
                    $provinceId,

                'call_off_id' =>
                    $callOffId,

                'delivery_receipt_id' =>
                    $deliveryReceiptId,

                'type' =>
                    $type,

                'title' =>
                    "Distribution Update: {$reference}",

                'message' =>
                    "Seeded distribution {$reference} currently has status {$status}.",

                'reference_type' =>
                    $deliveryReceiptId
                        ? 'App\\Models\\DeliveryReceipt'
                        : 'App\\Models\\CallOff',

                'reference_id' =>
                    $deliveryReceiptId
                        ?: $callOffId,

                'status' =>
                    'Unread',

                'read_at' =>
                    null,

                'resolved_at' =>
                    null,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);
    }
}