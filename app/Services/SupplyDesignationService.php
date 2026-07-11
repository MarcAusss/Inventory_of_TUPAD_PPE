<?php

namespace App\Services;

use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplyDesignationService extends BaseService
{
    public function __construct(
        private readonly InventoryMovementService $inventoryMovementService
    ) {}

    /**
     * Create a Project PPE Designation and deduct provincial inventory.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
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
            return DB::transaction(function () use (
                $data,
                $areDocument,
                $provinceId,
                &$documentPath
            ): SupplyDesignation {
                $submittedItems = $this->normalizeSubmittedItems(
                    $data['items']
                );

                $positiveItemIds = collect($submittedItems)
                    ->filter(
                        fn (int $quantity): bool => $quantity > 0
                    )
                    ->keys()
                    ->map(
                        fn ($itemId): int => (int) $itemId
                    )
                    ->values()
                    ->all();

                if ($positiveItemIds === []) {
                    throw ValidationException::withMessages([
                        'items' => 'Enter at least one PPE quantity greater than zero.',
                    ]);
                }

                $inventories = ProvincialInventory::query()
                    ->with('item')
                    ->where('province_id', $provinceId)
                    ->whereIn(
                        'item_id',
                        $positiveItemIds
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('item_id');

                $this->validateRequestedItems(
                    $inventories,
                    $submittedItems
                );

                $documentPath = $areDocument->store(
                    'are-documents',
                    'public'
                );

                if (! $documentPath) {
                    throw ValidationException::withMessages([
                        'are_document' => 'The ARE PDF could not be uploaded.',
                    ]);
                }

                $designation = SupplyDesignation::create([
                    /*
                     * New designations are based on total Provincial Inventory,
                     * not one specific Delivery Receipt.
                     */
                    'delivery_receipt_id' => null,

                    'province_id' => $provinceId,

                    'created_by' => $this->userId(),

                    /*
                     * Legacy columns remain synchronized for compatibility.
                     */
                    'designation_number' => $data['project_code'],

                    'project_name' => $data['project_title'],

                    'designation_date' => $data['designation_date'],

                    'project_code' => $data['project_code'],

                    'project_title' => $data['project_title'],

                    'location' => $data['location'],

                    'number_of_days' => (int) $data['number_of_days'],

                    'number_of_beneficiaries' => (int) $data['number_of_beneficiaries'],

                    'are_document' => $documentPath,

                    'status' => 'Completed',

                    'submitted_at' => now(),

                    'remarks' => $data['remarks'] ?? null,
                ]);

                foreach (
                    $submittedItems as $itemId => $quantity
                ) {
                    if ($quantity <= 0) {
                        continue;
                    }

                    /** @var ProvincialInventory|null $inventory */
                    $inventory = $inventories->get(
                        (int) $itemId
                    );

                    if (! $inventory) {
                        throw ValidationException::withMessages([
                            "items.{$itemId}" => 'This PPE item is not available in your provincial inventory.',
                        ]);
                    }

                    $designation->items()->create([
                        'item_id' => (int) $itemId,

                        'quantity' => $quantity,
                    ]);

                    /*
                     * Use a guarded update so the database cannot go below zero
                     * even if another request changes the stock concurrently.
                     */
                    $updatedRows = ProvincialInventory::query()
                        ->whereKey($inventory->id)
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
                        $itemName = $inventory
                            ->item
                            ?->item_name
                            ?? 'PPE item';

                        $label = $inventory
                            ->item
                            ?->label;

                        $displayName = $label
                            ? "{$itemName} ({$label})"
                            : $itemName;

                        throw ValidationException::withMessages([
                            "items.{$itemId}" => "{$displayName} no longer has enough available inventory. Refresh the page and try again.",
                        ]);
                    }
                }

                $designation->load([
                    'province',
                    'creator',
                    'items.item',
                ]);

                /*
                 * Record stock-out movements for the inventory ledger.
                 */
                $this
                    ->inventoryMovementService
                    ->recordSupplyDesignation($designation);

                return $designation->fresh([
                    'province',
                    'creator',
                    'items.item',
                ]);
            });
        } catch (Throwable $exception) {
            /*
             * Remove the uploaded ARE PDF if the database transaction fails.
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
     * Normalize item IDs and quantities.
     *
     * @param  array<int|string, mixed>  $submittedItems
     * @return array<int, int>
     */
    private function normalizeSubmittedItems(
        array $submittedItems
    ): array {
        $normalized = [];

        foreach ($submittedItems as $itemId => $quantity) {
            if (
                filter_var(
                    $itemId,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                throw ValidationException::withMessages([
                    'items' => 'One submitted PPE item identifier is invalid.',
                ]);
            }

            if (
                filter_var(
                    $quantity,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                throw ValidationException::withMessages([
                    "items.{$itemId}" => 'The PPE quantity must be a whole number.',
                ]);
            }

            $normalized[(int) $itemId] =
                (int) $quantity;
        }

        return $normalized;
    }

    /**
     * Validate project PPE quantities against current provincial inventory.
     *
     * @param  Collection<int, ProvincialInventory>  $inventories
     * @param  array<int, int>  $submittedItems
     */
    private function validateRequestedItems(
        Collection $inventories,
        array $submittedItems
    ): void {
        $errors = [];

        foreach (
            $submittedItems as $itemId => $quantity
        ) {
            if ($quantity < 0) {
                $errors[
                    "items.{$itemId}"
                ] = 'PPE quantities cannot be negative.';

                continue;
            }

            if ($quantity === 0) {
                continue;
            }

            /** @var ProvincialInventory|null $inventory */
            $inventory = $inventories->get(
                (int) $itemId
            );

            if (! $inventory) {
                $errors[
                    "items.{$itemId}"
                ] = 'This PPE item is not available in your provincial inventory.';

                continue;
            }

            if (
                $quantity
                > (int) $inventory->quantity
            ) {
                $itemName = $inventory
                    ->item
                    ?->item_name
                    ?? 'PPE item';

                $label = $inventory
                    ->item
                    ?->label;

                $displayName = $label
                    ? "{$itemName} ({$label})"
                    : $itemName;

                $errors[
                    "items.{$itemId}"
                ] = "{$displayName} has only "
                    ."{$inventory->quantity} available, "
                    ."but {$quantity} was requested.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }
}
