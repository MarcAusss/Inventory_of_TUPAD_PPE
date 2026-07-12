<?php

namespace App\Services;

use App\Models\ProvinceDistribution;
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
        private readonly InventoryMovementService $inventoryMovementService,
        private readonly CallOffInventoryService $callOffInventoryService
    ) {}

    /**
     * Create a Project PPE Designation using stock from one selected
     * Call-Off allocation.
     *
     * Available project quantity:
     *
     * Actual received under Call-Off
     * - previously distributed to projects
     * = available for the current project
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
            return DB::transaction(
                function () use (
                    $data,
                    $areDocument,
                    $provinceId,
                    &$documentPath
                ): SupplyDesignation {
                    $allocationId = (int) (
                        $data['province_distribution_id']
                        ?? 0
                    );

                    if ($allocationId <= 0) {
                        throw ValidationException::withMessages([
                            'province_distribution_id' => 'Please select a valid Call-Off allocation.',
                        ]);
                    }

                    /*
                     * Lock the selected allocation.
                     *
                     * This serializes project designation transactions using
                     * the same Call-Off and prevents concurrent over-issuing.
                     */
                    $allocation = ProvinceDistribution::query()
                        ->with([
                            'distributionBatch.callOff',
                            'distributionBatch.purchaseOrder.supplier',
                            'province',
                        ])
                        ->whereKey($allocationId)
                        ->where(
                            'province_id',
                            $provinceId
                        )
                        ->lockForUpdate()
                        ->first();

                    if (! $allocation) {
                        throw ValidationException::withMessages([
                            'province_distribution_id' => 'The selected Call-Off allocation does not belong to your Provincial Office.',
                        ]);
                    }

                    /*
                     * Lock the original allocation item records.
                     */
                    $allocationItems = $allocation
                        ->items()
                        ->with('item')
                        ->lockForUpdate()
                        ->get();

                    $allocation->setRelation(
                        'items',
                        $allocationItems
                    );

                    $this->validateAllocationStatus(
                        $allocation
                    );

                    $submittedItems =
                        $this->normalizeSubmittedItems(
                            $data['items'] ?? []
                        );

                    $positiveItems = collect(
                        $submittedItems
                    )->filter(
                        fn (int $quantity): bool => $quantity > 0
                    );

                    if ($positiveItems->isEmpty()) {
                        throw ValidationException::withMessages([
                            'items' => 'Enter at least one PPE quantity greater than zero.',
                        ]);
                    }

                    /*
                     * Recalculate the Call-Off balances after acquiring the
                     * allocation lock. This protects against stale browser data.
                     */
                    $balances =
                        $this->callOffInventoryService
                            ->balances($allocation);

                    $this->validateCallOffQuantities(
                        $balances,
                        $submittedItems
                    );

                    /*
                     * ProvincialInventory remains the official combined stock
                     * summary for the province. Lock its records as well.
                     */
                    $positiveItemIds = $positiveItems
                        ->keys()
                        ->map(
                            fn ($itemId): int => (int) $itemId
                        )
                        ->values()
                        ->all();

                    $inventories = ProvincialInventory::query()
                        ->with('item')
                        ->where(
                            'province_id',
                            $provinceId
                        )
                        ->whereIn(
                            'item_id',
                            $positiveItemIds
                        )
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('item_id');

                    $this->validateProvincialInventory(
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

                    $designation = SupplyDesignation::query()
                        ->create([
                            /*
                             * Legacy receipt reference remains null.
                             * The source is now the Province Distribution.
                             */
                            'delivery_receipt_id' => null,

                            'province_distribution_id' => $allocation->id,

                            'province_id' => $provinceId,

                            'created_by' => $this->userId(),

                            /*
                             * Legacy fields remain synchronized.
                             */
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
                         * Guarded pooled-inventory deduction.
                         *
                         * Even if another code path modifies the quantity,
                         * this condition prevents a negative stock balance.
                         */
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
                            $displayName =
                                $this->displayItemName(
                                    $inventory->item
                                );

                            throw ValidationException::withMessages([
                                "items.{$itemId}" => "{$displayName} no longer has enough provincial inventory. Refresh the page and try again.",
                            ]);
                        }
                    }

                    $designation->load([
                        'province',
                        'creator',
                        'items.item',
                        'provinceDistribution.province',
                        'provinceDistribution.distributionBatch.callOff',
                        'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                    ]);

                    /*
                     * Record one OUT inventory movement for each PPE item.
                     */
                    $this
                        ->inventoryMovementService
                        ->recordSupplyDesignation(
                            $designation
                        );

                    return $designation->fresh([
                        'province',
                        'creator',
                        'items.item',
                        'provinceDistribution.province',
                        'provinceDistribution.distributionBatch.callOff',
                        'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                    ]);
                },
                attempts: 3
            );
        } catch (Throwable $exception) {
            /*
             * Database rollback does not automatically delete a file.
             */
            if (
                $documentPath
                && Storage::disk('public')
                    ->exists($documentPath)
            ) {
                Storage::disk('public')
                    ->delete($documentPath);
            }

            throw $exception;
        }
    }

    /**
     * Confirm that the selected allocation has physically received PPE
     * and belongs to an approved or completed Call-Off.
     */
    private function validateAllocationStatus(
        ProvinceDistribution $allocation
    ): void {
        $callOff = $allocation
            ->distributionBatch
            ?->callOff;

        if (! $callOff) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'The selected allocation has no Call-Off record.',
            ]);
        }

        if (
            ! in_array(
                $callOff->status,
                [
                    'Approved',
                    'Completed',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'The selected Call-Off is not approved for project designation.',
            ]);
        }

        if (
            ! in_array(
                $allocation->status,
                [
                    'Partially Received',
                    'Received',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'province_distribution_id' => 'PPE must be physically received before it can be designated to a project.',
            ]);
        }
    }

    /**
     * Normalize item identifiers and submitted quantities.
     *
     * @param  array<int|string, mixed>  $submittedItems
     * @return array<int, int>
     */
    private function normalizeSubmittedItems(
        array $submittedItems
    ): array {
        $normalized = [];

        foreach (
            $submittedItems as $itemId => $quantity
        ) {
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
     * Validate against the exact stock available from the selected
     * Call-Off.
     *
     * @param  array<int, array<string, mixed>>  $balances
     * @param  array<int, int>  $submittedItems
     */
    private function validateCallOffQuantities(
        array $balances,
        array $submittedItems
    ): void {
        $errors = [];

        foreach (
            $submittedItems as $itemId => $quantity
        ) {
            if ($quantity < 0) {
                $errors[
                    "items.{$itemId}"
                ] =
                    'PPE quantities cannot be negative.';

                continue;
            }

            if ($quantity === 0) {
                continue;
            }

            if (! isset($balances[$itemId])) {
                $errors[
                    "items.{$itemId}"
                ] =
                    'This PPE item does not belong to the selected Call-Off allocation.';

                continue;
            }

            $available = (int) $balances[
                $itemId
            ]['available_for_projects'];

            if ($quantity > $available) {
                $displayName =
                    $this->displayItemName(
                        $balances[
                            $itemId
                        ]['item']
                    );

                $errors[
                    "items.{$itemId}"
                ] =
                    "{$displayName} has only "
                    .number_format($available)
                    .' available from the selected Call-Off, '
                    .number_format($quantity)
                    .' was requested.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /**
     * Validate the same quantities against the province-wide pooled
     * inventory before deducting.
     *
     * @param  Collection<int, ProvincialInventory>  $inventories
     * @param  array<int, int>  $submittedItems
     */
    private function validateProvincialInventory(
        Collection $inventories,
        array $submittedItems
    ): void {
        $errors = [];

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
                $errors[
                    "items.{$itemId}"
                ] =
                    'This PPE item is not available in your provincial inventory.';

                continue;
            }

            if (
                $quantity
                > (int) $inventory->quantity
            ) {
                $displayName =
                    $this->displayItemName(
                        $inventory->item
                    );

                $errors[
                    "items.{$itemId}"
                ] =
                    "{$displayName} has only "
                    .number_format(
                        $inventory->quantity
                    )
                    .' available in the combined provincial inventory.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    private function displayItemName(
        mixed $item
    ): string {
        if (! $item) {
            return 'PPE item';
        }

        $itemName =
            $item->item_name
            ?? 'PPE item';

        $label =
            $item->label
            ?? null;

        return $label
            ? "{$itemName} ({$label})"
            : $itemName;
    }
}
