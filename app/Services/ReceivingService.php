<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptItem;
use App\Models\ProvinceDistribution;
use App\Models\ProvincialInventory;
use Illuminate\Support\Collection;
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
     * Receive one physical delivery under an approved provincial allocation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function receive(
        ProvinceDistribution $provinceDistribution,
        array $data,
        array $documents
    ): DeliveryReceipt {
        $this->requireProvincial();

        $storedDocumentPaths = [];

        try {
            return DB::transaction(
                function () use (
                    $provinceDistribution,
                    $data,
                    $documents,
                    &$storedDocumentPaths
                ): DeliveryReceipt {
                    /*
                     * Lock the provincial allocation to prevent two receiving
                     * requests from updating it simultaneously.
                     */
                    $provinceDistribution =
                        ProvinceDistribution::query()
                            ->with([
                                'distributionBatch.callOff',
                                'distributionBatch.purchaseOrder',
                                'distributionBatch.provinceDistributions',
                                'province',
                            ])
                            ->lockForUpdate()
                            ->findOrFail(
                                $provinceDistribution->id
                            );

                    /*
                     * Lock allocation item rows.
                     */
                    $allocationItems =
                        $provinceDistribution
                            ->items()
                            ->with('item')
                            ->lockForUpdate()
                            ->get();

                    $provinceDistribution->setRelation(
                        'items',
                        $allocationItems
                    );

                    $this->validateProvinceAccess(
                        $provinceDistribution
                    );

                    $this->validateAllocationStatus(
                        $provinceDistribution
                    );

                    /*
                     * Calculate all quantities already received under earlier
                     * Delivery Receipts.
                     */
                    $previouslyReceived =
                        $this->previouslyReceivedByItem(
                            $provinceDistribution,
                            $allocationItems
                        );

                    $this->validateReceivedItems(
                        $provinceDistribution,
                        $data['items'] ?? [],
                        $previouslyReceived
                    );

                    foreach ($documents as $document) {
                        $path = $document->store('delivery-receipts', 'local');

                        if (! $path) {
                            throw ValidationException::withMessages([
                                'documents' => 'One of the receiving documents could not be uploaded.',
                            ]);
                        }

                        $storedDocumentPaths[] = [
                            'original_name' => $document->getClientOriginalName(),
                            'file_path' => $path,
                            'mime_type' => $document->getClientMimeType(),
                            'file_size' => $document->getSize(),
                        ];
                    }

                    $purchaseOrder =
                        $provinceDistribution
                            ->distributionBatch
                            ?->purchaseOrder;

                    if (! $purchaseOrder) {
                        throw ValidationException::withMessages([
                            'province_distribution' => 'The source Purchase Order could not be found.',
                        ]);
                    }

                    $deliveryDate =
                        $data['delivery_date']
                        ?? $provinceDistribution->scheduled_delivery_date;

                    if (! $deliveryDate) {
                        throw ValidationException::withMessages([
                            'delivery_date' => 'The delivery date is required. Ask TSSD to set the scheduled delivery date for this provincial allocation.',
                        ]);
                    }

                    $receipt = DeliveryReceipt::query()
                        ->create([
                            'province_distribution_id' => $provinceDistribution->id,

                            'purchase_order_id' => $purchaseOrder->id,

                            'province_id' => $provinceDistribution->province_id,

                            'received_by_user_id' => $this->userId(),

                            'physical_receiver_name' => $data['physical_receiver_name'],

                            'dr_number' => $data['dr_number'],

                            /*
                             * Use the per-province delivery schedule created
                             * by TSSD. A submitted delivery_date may override
                             * it when the receiving form intentionally allows
                             * the Provincial Office to record the actual date.
                             */
                            'delivery_date' => $deliveryDate,

                            'document' => $storedDocumentPaths[0]['file_path'] ?? null,

                            /*
                             * Legacy compatibility field.
                             */
                            'received_by' => $data['physical_receiver_name'],

                            'remarks' => $data['remarks'] ?? null,

                            'status' => 'Received',

                            'submitted_at' => now(),
                        ]);

                    foreach ($storedDocumentPaths as $documentData) {
                        $receipt->documents()->create($documentData);
                    }

                    foreach (
                        $allocationItems as $allocationItem
                    ) {
                        $receivedQuantity = (int) (
                            $data['items'][
                                $allocationItem->id
                            ] ?? 0
                        );

                        $receipt
                            ->items()
                            ->create([
                                'province_distribution_item_id' => $allocationItem->id,

                                'item_id' => $allocationItem->item_id,

                                /*
                                 * Retain the legacy quantity column.
                                 */
                                'quantity' => $receivedQuantity,

                                /*
                                 * Keep the original Call-Off allocation as
                                 * the reference quantity on every DR row.
                                 */
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
                        'provinceDistribution'
                            .'.distributionBatch'
                            .'.callOff',
                        'provinceDistribution'
                            .'.distributionBatch'
                            .'.purchaseOrder'
                            .'.supplier',
                    ]);

                    /*
                     * Recalculate cumulative totals after the new receipt
                     * has been inserted.
                     */
                    $updatedReceived =
                        $this->previouslyReceivedByItem(
                            $provinceDistribution,
                            $allocationItems
                        );

                    $fullyReceived =
                        $allocationItems->every(
                            function (
                                $allocationItem
                            ) use (
                                $updatedReceived
                            ): bool {
                                $received = (int) (
                                    $updatedReceived[
                                        $allocationItem->id
                                    ] ?? 0
                                );

                                return $received
                                    >= (int) $allocationItem->quantity;
                            }
                        );

                    $provinceDistribution->update([
                        'status' => $fullyReceived
                            ? 'Received'
                            : 'Partially Received',

                        'received_at' => now(),

                        'remarks' => $this->mergeRemarks(
                            $provinceDistribution->remarks,
                            $data['remarks'] ?? null,
                            $receipt->dr_number
                        ),
                    ]);

                    $this->updateBatchReceivingStatus(
                        $provinceDistribution
                    );

                    /*
                     * Creates one IN movement per positive received item.
                     * Each DR has its own movement reference.
                     */
                    $this
                        ->inventoryMovementService
                        ->recordDeliveryReceipt(
                            $receipt
                        );

                    $this
                        ->notificationService
                        ->notifyTssdOfReceiving(
                            $receipt
                        );

                    return $receipt->fresh([
                        'provinceDistribution'
                            .'.distributionBatch'
                            .'.callOff',

                        'provinceDistribution'
                            .'.distributionBatch'
                            .'.purchaseOrder'
                            .'.supplier',

                        'province',
                        'receivedByUser',
                        'items.item',
                    ]);
                },
                attempts: 3
            );
        } catch (Throwable $exception) {
            /*
             * A database rollback does not remove an uploaded file.
             */
            foreach ($storedDocumentPaths as $documentData) {
                $path = $documentData['file_path'] ?? null;

                if ($path && Storage::disk('local')->exists($path) || Storage::disk('public')->exists($path)) {
                    Storage::disk(Storage::disk('local')->exists($path) ? 'local' : 'public')->delete($path);
                }
            }

            throw $exception;
        }
    }

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

    private function validateAllocationStatus(
        ProvinceDistribution $provinceDistribution
    ): void {
        $callOff =
            $provinceDistribution
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

        if (
            ! $provinceDistribution
                ->canBeReceived()
        ) {
            throw ValidationException::withMessages([
                'province_distribution' => 'This allocation cannot be received while its status is '
                    ."{$provinceDistribution->status}.",
            ]);
        }
    }

    /**
     * Get cumulative received quantities grouped by allocation item.
     *
     * @param  Collection<int, mixed>  $allocationItems
     * @return Collection<int|string, int>
     */
    private function previouslyReceivedByItem(
        ProvinceDistribution $provinceDistribution,
        Collection $allocationItems
    ): Collection {
        $allocationItemIds =
            $allocationItems
                ->pluck('id')
                ->map(
                    fn ($id): int => (int) $id
                )
                ->values()
                ->all();

        if ($allocationItemIds === []) {
            return collect();
        }

        /*
         * Load and lock previous DR item rows so another transaction cannot
         * change cumulative totals while the new receipt is being processed.
         */
        return DeliveryReceiptItem::query()
            ->whereIn(
                'province_distribution_item_id',
                $allocationItemIds
            )
            ->whereHas(
                'deliveryReceipt',
                fn ($query) => $query->where(
                    'province_distribution_id',
                    $provinceDistribution->id
                )
            )
            ->lockForUpdate()
            ->get()
            ->groupBy(
                'province_distribution_item_id'
            )
            ->map(
                fn ($items): int => (int) $items->sum(
                    'received_quantity'
                )
            );
    }

    /**
     * @param  array<int|string, mixed>  $submittedItems
     * @param  Collection<int|string, int>  $previouslyReceived
     */
    private function validateReceivedItems(
        ProvinceDistribution $provinceDistribution,
        array $submittedItems,
        Collection $previouslyReceived
    ): void {
        $errors = [];

        $positiveTotal = collect(
            $submittedItems
        )
            ->map(
                fn ($quantity): int => (int) $quantity
            )
            ->filter(
                fn (int $quantity): bool => $quantity > 0
            )
            ->sum();

        if ($positiveTotal <= 0) {
            $errors['items'] =
                'Enter at least one received PPE quantity greater than zero.';
        }

        foreach (
            $provinceDistribution->items as $allocationItem
        ) {
            $field =
                "items.{$allocationItem->id}";

            if (
                ! array_key_exists(
                    $allocationItem->id,
                    $submittedItems
                )
            ) {
                $errors[$field] =
                    'A received quantity is required for every assigned PPE item.';

                continue;
            }

            $submittedQuantity =
                $submittedItems[
                    $allocationItem->id
                ];

            if (
                filter_var(
                    $submittedQuantity,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                $errors[$field] =
                    'The received quantity must be a whole number.';

                continue;
            }

            $receivedQuantity =
                (int) $submittedQuantity;

            if ($receivedQuantity < 0) {
                $errors[$field] =
                    'Received quantities cannot be negative.';

                continue;
            }

            $alreadyReceived = (int) (
                $previouslyReceived[
                    $allocationItem->id
                ] ?? 0
            );

            $remainingReceivable = max(
                0,
                (int) $allocationItem->quantity
                    - $alreadyReceived
            );

            if (
                $receivedQuantity
                > $remainingReceivable
            ) {
                $itemName =
                    $allocationItem
                        ->item
                        ?->item_name
                    ?? 'PPE item';

                $label =
                    $allocationItem
                        ->item
                        ?->label;

                $displayName = $label
                    ? "{$itemName} ({$label})"
                    : $itemName;

                $errors[$field] =
                    "{$displayName} has only "
                    .number_format(
                        $remainingReceivable
                    )
                    .' remaining to receive. '
                    .number_format(
                        $alreadyReceived
                    )
                    .' has already been recorded.';
            }
        }

        $validIds = $provinceDistribution
            ->items
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->values()
            ->all();

        foreach (
            array_keys($submittedItems) as $submittedId
        ) {
            if (
                ! in_array(
                    (int) $submittedId,
                    $validIds,
                    true
                )
            ) {
                $errors[
                    "items.{$submittedId}"
                ] =
                    'One submitted PPE item does not belong to this allocation.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    private function increaseProvincialInventory(
        int $provinceId,
        int $itemId,
        int $quantity
    ): void {
        $inventory =
            ProvincialInventory::query()
                ->where(
                    'province_id',
                    $provinceId
                )
                ->where(
                    'item_id',
                    $itemId
                )
                ->lockForUpdate()
                ->first();

        if (! $inventory) {
            ProvincialInventory::query()
                ->create([
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

    private function updateBatchReceivingStatus(
        ProvinceDistribution $provinceDistribution
    ): void {
        $batch =
            $provinceDistribution
                ->distributionBatch;

        if (! $batch) {
            return;
        }

        $batch->load([
            'provinceDistributions',
            'callOff',
        ]);

        $allCompletelyReceived =
            $batch
                ->provinceDistributions
                ->every(
                    fn (
                        ProvinceDistribution $allocation
                    ): bool => $allocation->status
                            === 'Received'
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

        $hasReceiving =
            $batch
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

    private function mergeRemarks(
        ?string $existingRemarks,
        ?string $receivingRemarks,
        string $drNumber
    ): ?string {
        $receivingRemarks = trim(
            (string) $receivingRemarks
        );

        if ($receivingRemarks === '') {
            return $existingRemarks;
        }

        $entry = sprintf(
            "[DR %s - %s]\n%s",
            $drNumber,
            now()->format(
                'Y-m-d H:i:s'
            ),
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