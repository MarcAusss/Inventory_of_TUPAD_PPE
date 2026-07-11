<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use App\Models\ProvincialInventory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReceivingService extends BaseService
{
    public function __construct(
        private readonly WorkflowNotificationService $notificationService,
        private readonly InventoryMovementService $inventoryMovementService
    ) {}

    /**
     * Receive one approved provincial allocation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function receive(
        ProvinceDistribution $provinceDistribution,
        array $data,
        UploadedFile $document
    ): DeliveryReceipt {
        $this->requireProvincial();

        $documentPath = null;

        try {
            return DB::transaction(function () use (
                $provinceDistribution,
                $data,
                $document,
                &$documentPath
            ): DeliveryReceipt {
                $provinceDistribution = ProvinceDistribution::query()
                    ->with([
                        'distributionBatch.callOff',
                        'distributionBatch.purchaseOrder',
                        'distributionBatch.provinceDistributions',
                        'province',
                        'items.item',
                        'deliveryReceipt',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($provinceDistribution->id);

                $this->validateProvinceAccess(
                    $provinceDistribution
                );

                $this->validateAllocationStatus(
                    $provinceDistribution
                );

                $this->validateNoExistingReceipt(
                    $provinceDistribution
                );

                $this->validateReceivedItems(
                    $provinceDistribution,
                    $data['items']
                );

                $documentPath = $document->store(
                    'delivery-receipts',
                    'public'
                );

                if (! $documentPath) {
                    throw ValidationException::withMessages([
                        'document' => 'The Delivery Receipt PDF could not be uploaded.',
                    ]);
                }

                $purchaseOrder = $provinceDistribution
                    ->distributionBatch
                    ?->purchaseOrder;

                if (! $purchaseOrder) {
                    throw ValidationException::withMessages([
                        'province_distribution' => 'The source Purchase Order could not be found.',
                    ]);
                }

                $receipt = DeliveryReceipt::create([
                    'province_distribution_id' => $provinceDistribution->id,

                    /*
                     * Retained for compatibility and reporting.
                     */
                    'purchase_order_id' => $purchaseOrder->id,

                    'province_id' => $provinceDistribution->province_id,

                    'received_by_user_id' => $this->userId(),

                    'physical_receiver_name' => $data['physical_receiver_name'],

                    'dr_number' => $data['dr_number'],

                    'delivery_date' => $data['delivery_date'],

                    'document' => $documentPath,

                    /*
                     * Legacy field retained while older pages still use it.
                     */
                    'received_by' => $data['physical_receiver_name'],

                    'remarks' => $data['remarks'] ?? null,

                    'status' => 'Received',

                    'submitted_at' => now(),
                ]);

                foreach (
                    $provinceDistribution->items as $allocationItem
                ) {
                    $receivedQuantity = (int) (
                        $data['items'][$allocationItem->id]
                        ?? 0
                    );

                    $receipt->items()->create([
                        'province_distribution_item_id' => $allocationItem->id,

                        'item_id' => $allocationItem->item_id,

                        /*
                         * Legacy quantity mirrors the actual received quantity.
                         */
                        'quantity' => $receivedQuantity,

                        'assigned_quantity' => (int) $allocationItem->quantity,

                        'received_quantity' => $receivedQuantity,
                    ]);

                    if ($receivedQuantity > 0) {
                        $this->increaseProvincialInventory(
                            (int) $provinceDistribution->province_id,
                            (int) $allocationItem->item_id,
                            $receivedQuantity
                        );
                    }
                }

                $receipt->load([
                    'province',
                    'items.item',
                    'receivedByUser',
                    'provinceDistribution.distributionBatch.callOff',
                    'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                ]);

                $hasDiscrepancy = $receipt
                    ->items
                    ->contains(
                        fn ($item): bool => (int) $item->assigned_quantity
                            !== (int) $item->received_quantity
                    );

                $provinceDistribution->update([
                    'status' => $hasDiscrepancy
                        ? 'Partially Received'
                        : 'Received',

                    'received_at' => now(),

                    'remarks' => $this->mergeRemarks(
                        $provinceDistribution->remarks,
                        $data['remarks'] ?? null
                    ),
                ]);

                $this->updateBatchReceivingStatus(
                    $provinceDistribution
                );

                /*
                 * Record stock-in entries for the inventory ledger.
                 */
                $this
                    ->inventoryMovementService
                    ->recordDeliveryReceipt($receipt);

                /*
                 * Create the TSSD receiving inbox notification.
                 */
                $this
                    ->notificationService
                    ->notifyTssdOfReceiving($receipt);

                return $receipt->fresh([
                    'provinceDistribution.distributionBatch.callOff',
                    'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                    'province',
                    'receivedByUser',
                    'items.item',
                ]);
            });
        } catch (Throwable $exception) {
            /*
             * Database transactions cannot automatically remove a file that
             * was already stored. Delete it if the transaction fails.
             */
            if (
                $documentPath
                && Storage::disk('public')->exists($documentPath)
            ) {
                Storage::disk('public')->delete($documentPath);
            }

            throw $exception;
        }
    }

    /**
     * Ensure the logged-in Provincial Office owns the allocation.
     */
    private function validateProvinceAccess(
        ProvinceDistribution $provinceDistribution
    ): void {
        $provinceId = $this->provinceId();

        if (! $provinceId) {
            abort(
                403,
                'The Provincial Office account is not assigned to a province.'
            );
        }

        abort_unless(
            (int) $provinceDistribution->province_id
                === (int) $provinceId,
            403,
            'You cannot receive PPE assigned to another province.'
        );
    }

    /**
     * Ensure the allocation has an approved Call-Off and may be received.
     */
    private function validateAllocationStatus(
        ProvinceDistribution $provinceDistribution
    ): void {
        $callOff = $provinceDistribution
            ->distributionBatch
            ?->callOff;

        if (
            ! $callOff
            || $callOff->status !== 'Approved'
        ) {
            throw ValidationException::withMessages([
                'province_distribution' => 'This allocation does not have an approved Call-Off.',
            ]);
        }

        if (! $provinceDistribution->canBeReceived()) {
            throw ValidationException::withMessages([
                'province_distribution' => "This allocation cannot be received while its status is {$provinceDistribution->status}.",
            ]);
        }
    }

    /**
     * Prevent duplicate receiving.
     */
    private function validateNoExistingReceipt(
        ProvinceDistribution $provinceDistribution
    ): void {
        if (
            $provinceDistribution->deliveryReceipt
            || DeliveryReceipt::query()
                ->where(
                    'province_distribution_id',
                    $provinceDistribution->id
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'province_distribution' => 'This provincial allocation already has a Delivery Receipt.',
            ]);
        }
    }

    /**
     * Validate every submitted quantity against the provincial allocation.
     *
     * @param  array<int|string, mixed>  $submittedItems
     */
    private function validateReceivedItems(
        ProvinceDistribution $provinceDistribution,
        array $submittedItems
    ): void {
        $errors = [];

        foreach (
            $provinceDistribution->items as $allocationItem
        ) {
            if (! array_key_exists(
                $allocationItem->id,
                $submittedItems
            )) {
                $errors[
                    "items.{$allocationItem->id}"
                ] = 'A received quantity is required for every assigned PPE item.';

                continue;
            }

            $submittedQuantity =
                $submittedItems[$allocationItem->id];

            if (
                filter_var(
                    $submittedQuantity,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                $errors[
                    "items.{$allocationItem->id}"
                ] = 'The received quantity must be a whole number.';

                continue;
            }

            $receivedQuantity = (int) $submittedQuantity;

            if ($receivedQuantity < 0) {
                $errors[
                    "items.{$allocationItem->id}"
                ] = 'Received quantities cannot be negative.';

                continue;
            }

            if (
                $receivedQuantity
                > (int) $allocationItem->quantity
            ) {
                $itemName = $allocationItem
                    ->item
                    ?->item_name
                    ?? 'PPE item';

                $label = $allocationItem
                    ->item
                    ?->label;

                $displayName = $label
                    ? "{$itemName} ({$label})"
                    : $itemName;

                $errors[
                    "items.{$allocationItem->id}"
                ] = "{$displayName} has an assigned quantity of "
                    ."{$allocationItem->quantity}, but "
                    ."{$receivedQuantity} was submitted.";
            }
        }

        $validIds = $provinceDistribution
            ->items
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->all();

        foreach (
            array_keys($submittedItems) as $submittedId
        ) {
            if (! in_array(
                (int) $submittedId,
                $validIds,
                true
            )) {
                $errors[
                    "items.{$submittedId}"
                ] = 'One submitted PPE item does not belong to this provincial allocation.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /**
     * Increase or create the current Provincial Inventory record.
     */
    private function increaseProvincialInventory(
        int $provinceId,
        int $itemId,
        int $quantity
    ): void {
        $inventory = ProvincialInventory::query()
            ->where('province_id', $provinceId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            ProvincialInventory::create([
                'province_id' => $provinceId,
                'item_id' => $itemId,
                'quantity' => $quantity,
            ]);

            return;
        }

        $inventory->increment(
            'quantity',
            $quantity
        );
    }

    /**
     * Update the overall distribution batch and Call-Off statuses.
     */
    private function updateBatchReceivingStatus(
        ProvinceDistribution $provinceDistribution
    ): void {
        $batch = $provinceDistribution
            ->distributionBatch;

        if (! $batch) {
            return;
        }

        $batch->load([
            'provinceDistributions',
            'callOff',
        ]);

        $allCompletelyReceived = $batch
            ->provinceDistributions
            ->every(
                fn (
                    ProvinceDistribution $allocation
                ): bool => $allocation->status === 'Received'
            );

        if ($allCompletelyReceived) {
            $batch->update([
                'status' => 'Completed',
            ]);

            $batch->callOff?->update([
                'status' => 'Completed',
            ]);

            return;
        }

        $hasReceiving = $batch
            ->provinceDistributions
            ->contains(
                fn (
                    ProvinceDistribution $allocation
                ): bool => in_array(
                    $allocation->status,
                    [
                        'Received',
                        'Partially Received',
                    ],
                    true
                )
            );

        if ($hasReceiving) {
            $batch->update([
                'status' => 'Partially Received',
            ]);
        }
    }

    /**
     * Preserve earlier remarks and append the receiving remarks.
     */
    private function mergeRemarks(
        ?string $existingRemarks,
        ?string $receivingRemarks
    ): ?string {
        $receivingRemarks = trim(
            (string) $receivingRemarks
        );

        if ($receivingRemarks === '') {
            return $existingRemarks;
        }

        $entry = sprintf(
            "[Provincial Receiving - %s]\n%s",
            now()->format('Y-m-d H:i:s'),
            $receivingRemarks
        );

        if (! $existingRemarks) {
            return $entry;
        }

        return $existingRemarks
            ."\n\n"
            .$entry;
    }
}
