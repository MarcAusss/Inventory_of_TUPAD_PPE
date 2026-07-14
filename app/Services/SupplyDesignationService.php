<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplyDesignationService extends BaseService
{
    public function __construct(
        private readonly DeliveryReceiptInventoryService $deliveryReceiptInventoryService,

        private readonly InventoryMovementService $inventoryMovementService
    ) {}

    /**
     * Create a completed Project PPE Designation using one exact
     * Delivery Receipt as its inventory source.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(
        array $data,
        UploadedFile $areDocument
    ): SupplyDesignation {
        $this->requireProvincial();

        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $documentPath = null;

        try {
            $designation = DB::transaction(
                function () use (
                    $data,
                    $areDocument,
                    $provinceId,
                    &$documentPath
                ): SupplyDesignation {
                    $receipt = DeliveryReceipt::query()
                        ->with([
                            'items.item',
                            'provinceDistribution.distributionBatch.callOff',
                        ])
                        ->whereKey(
                            (int) $data[
                                'delivery_receipt_id'
                            ]
                        )
                        ->where(
                            'province_id',
                            $provinceId
                        )
                        ->where(
                            'status',
                            'Received'
                        )
                        ->lockForUpdate()
                        ->first();

                    if (! $receipt) {
                        throw ValidationException::withMessages([
                            'delivery_receipt_id' => 'The selected Delivery Receipt is unavailable.',
                        ]);
                    }

                    if (! $receipt->province_distribution_id) {
                        throw ValidationException::withMessages([
                            'delivery_receipt_id' => 'The selected Delivery Receipt is not linked to a Call-Off allocation.',
                        ]);
                    }

                    $callOff = $receipt
                        ->provinceDistribution
                        ?->distributionBatch
                        ?->callOff;

                    if (
                        ! $callOff
                        || ! in_array(
                            $callOff->status,
                            [
                                'Approved',
                                'Completed',
                            ],
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'delivery_receipt_id' => 'The selected Delivery Receipt does not belong to an approved Call-Off.',
                        ]);
                    }

                    $submittedItems = collect(
                        $data['items']
                        ?? []
                    )
                        ->map(
                            fn ($quantity): int => (int) $quantity
                        )
                        ->filter(
                            fn (
                                int $quantity
                            ): bool => $quantity > 0
                        )
                        ->all();

                    $this->deliveryReceiptInventoryService
                        ->validateProjectQuantities(
                            $receipt,
                            $submittedItems
                        );

                    $itemIds = array_map(
                        'intval',
                        array_keys($submittedItems)
                    );

                    $inventories =
                        ProvincialInventory::query()
                            ->with('item')
                            ->where(
                                'province_id',
                                $provinceId
                            )
                            ->whereIn(
                                'item_id',
                                $itemIds
                            )
                            ->lockForUpdate()
                            ->get()
                            ->keyBy('item_id');

                    foreach (
                        $submittedItems as $itemId => $quantity
                    ) {
                        $inventory = $inventories->get(
                            (int) $itemId
                        );

                        if (! $inventory) {
                            throw ValidationException::withMessages([
                                "items.{$itemId}" => 'The provincial inventory record could not be found.',
                            ]);
                        }

                        if (
                            (int) $inventory->quantity
                            < (int) $quantity
                        ) {
                            throw ValidationException::withMessages([
                                "items.{$itemId}" => 'The current provincial inventory is no longer sufficient. Refresh the page and try again.',
                            ]);
                        }
                    }

                    $documentPath = $areDocument->store(
                        'are-documents',
                        'public'
                    );

                    if (! $documentPath) {
                        throw ValidationException::withMessages([
                            'are_document' => 'The ARE PDF could not be uploaded.',
                        ]);
                    }

                    $designation =
                        SupplyDesignation::query()->create([
                            /*
                             * Exact source receipt.
                             */
                            'delivery_receipt_id' => $receipt->id,

                            /*
                             * Parent Call-Off allocation retained for
                             * reporting and relationship navigation.
                             */
                            'province_distribution_id' => $receipt
                                ->province_distribution_id,

                            'province_id' => $provinceId,

                            'created_by' => $this->userId(),

                            'designation_number' => $data['project_code'],

                            'project_name' => $data['project_title'],

                            'designation_date' => $data['designation_date'],

                            'project_code' => $data['project_code'],

                            'project_title' => $data['project_title'],

                            'location' => $data['location'],

                            'number_of_days' => (int) $data[
                                    'number_of_days'
                                ],

                            'number_of_beneficiaries' => (int) $data[
                                    'number_of_beneficiaries'
                                ],

                            'are_document' => $documentPath,

                            'status' => 'Completed',

                            'submitted_at' => now(),

                            'remarks' => $data['remarks']
                                ?? null,
                        ]);

                    foreach (
                        $submittedItems as $itemId => $quantity
                    ) {
                        $itemId = (int) $itemId;

                        $quantity = (int) $quantity;

                        $designation->items()->create([
                            'item_id' => $itemId,

                            'quantity' => $quantity,
                        ]);

                        $inventory = $inventories->get(
                            $itemId
                        );

                        $updatedRows =
                            ProvincialInventory::query()
                                ->whereKey(
                                    $inventory->id
                                )
                                ->where(
                                    'province_id',
                                    $provinceId
                                )
                                ->where(
                                    'quantity',
                                    '>=',
                                    $quantity
                                )
                                ->decrement(
                                    'quantity',
                                    $quantity
                                );

                        if ($updatedRows !== 1) {
                            throw ValidationException::withMessages([
                                "items.{$itemId}" => 'The inventory changed while processing. Refresh the page and try again.',
                            ]);
                        }
                    }

                    $designation->load([
                        'province',
                        'creator',
                        'items.item',
                        'deliveryReceipt.items.item',
                        'provinceDistribution.distributionBatch.callOff',
                        'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                    ]);

                    $this->inventoryMovementService
                        ->recordSupplyDesignation(
                            $designation
                        );

                    return $designation;
                }
            );

            return $designation;
        } catch (Throwable $exception) {
            if ($documentPath) {
                Storage::disk('public')
                    ->delete(
                        $documentPath
                    );
            }

            throw $exception;
        }
    }
}
