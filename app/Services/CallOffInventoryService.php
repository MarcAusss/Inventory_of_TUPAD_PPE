<?php

namespace App\Services;

use App\Models\DeliveryReceiptItem;
use App\Models\ProvinceDistribution;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignationItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CallOffInventoryService extends BaseService
{
    /**
     * Return Call-Off allocations that still have PPE available
     * for project designation.
     *
     * The available quantity is limited by both:
     *
     * 1. Remaining PPE under the selected Call-Off.
     * 2. Current province-wide pooled inventory.
     *
     * @return Collection<int, ProvinceDistribution>
     */
    public function availableAllocations(): Collection
    {
        $this->requireProvincial();

        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $allocations = ProvinceDistribution::query()
            ->with([
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'province',
                'items.item',
                'deliveryReceipts.items',
                'supplyDesignations.items',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereIn(
                'status',
                [
                    'Partially Received',
                    'Received',
                ]
            )
            ->whereHas(
                'distributionBatch.callOff',
                function ($query): void {
                    $query->whereIn(
                        'status',
                        [
                            'Approved',
                            'Completed',
                        ]
                    );
                }
            )
            ->latest('received_at')
            ->latest('id')
            ->get();

        return $allocations
            ->map(
                function (
                    ProvinceDistribution $allocation
                ): ProvinceDistribution {
                    $balances = $this->balances(
                        $allocation
                    );

                    $availableTotal = collect(
                        $balances
                    )->sum(
                        'available_for_projects'
                    );

                    $allocation->setAttribute(
                        'call_off_balances',
                        $balances
                    );

                    $allocation->setAttribute(
                        'available_for_projects_total',
                        (int) $availableTotal
                    );

                    return $allocation;
                }
            )
            ->filter(
                fn (
                    ProvinceDistribution $allocation
                ): bool => (int) $allocation
                    ->available_for_projects_total > 0
            )
            ->values();
    }

    /**
     * Build the inventory balances for one selected Call-Off.
     *
     * Call-Off available:
     *
     * Actual received under the selected Call-Off
     * - Completed project designations linked to the selected Call-Off
     *
     * Effective available:
     *
     * Minimum of:
     * - Call-Off available
     * - Current pooled provincial inventory
     *
     * The pooled limitation is required because legacy completed
     * designations may have reduced the provincial inventory without
     * being linked to a specific Call-Off.
     *
     * @return array<int, array<string, mixed>>
     */
    public function balances(
        ProvinceDistribution $allocation
    ): array {
        $this->ensureProvinceAccess(
            $allocation
        );

        $allocation->loadMissing([
            'distributionBatch.callOff',
            'distributionBatch.purchaseOrder.supplier',
            'items.item',
        ]);

        $allocationItemIds = $allocation
            ->items
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->values()
            ->all();

        $itemIds = $allocation
            ->items
            ->pluck('item_id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->values()
            ->all();

        $receivedByAllocationItem =
            $this->receivedByAllocationItem(
                $allocation,
                $allocationItemIds
            );

        $distributedByItem =
            $this->distributedByItem(
                $allocation,
                $itemIds
            );

        /*
         * Get the real current province-wide inventory.
         *
         * This already includes the effects of:
         * - all Delivery Receipts
         * - all completed project designations
         * - legacy designations not linked to Call-Offs
         */
        $pooledInventoryByItem =
            ProvincialInventory::query()
                ->where(
                    'province_id',
                    $allocation->province_id
                )
                ->whereIn(
                    'item_id',
                    $itemIds
                )
                ->pluck(
                    'quantity',
                    'item_id'
                )
                ->map(
                    fn ($quantity): int => (int) $quantity
                );

        $balances = [];

        foreach (
            $allocation->items as $allocationItem
        ) {
            $allocationItemId =
                (int) $allocationItem->id;

            $itemId =
                (int) $allocationItem->item_id;

            $allocated =
                (int) $allocationItem->quantity;

            $actualReceived = (int) (
                $receivedByAllocationItem[
                    $allocationItemId
                ] ?? 0
            );

            $previouslyDistributed = (int) (
                $distributedByItem[
                    $itemId
                ] ?? 0
            );

            /*
             * PPE allocated but not physically received yet.
             */
            $remainingReceivable = max(
                0,
                $allocated - $actualReceived
            );

            /*
             * PPE remaining according to the selected Call-Off only.
             */
            $callOffAvailable = max(
                0,
                $actualReceived
                    - $previouslyDistributed
            );

            /*
             * Actual PPE currently available across the whole province.
             */
            $pooledAvailable = max(
                0,
                (int) (
                    $pooledInventoryByItem[
                        $itemId
                    ] ?? 0
                )
            );

            /*
             * Safe quantity that may be distributed right now.
             *
             * Example:
             *
             * Call-Off available = 40
             * Pooled available   = 16
             *
             * Effective available = 16
             */
            $effectiveAvailable = min(
                $callOffAvailable,
                $pooledAvailable
            );

            /*
             * This represents Call-Off stock that cannot currently be
             * issued because province-wide stock was previously consumed
             * by legacy or unassigned project designations.
             */
            $legacyOrUnassignedReserve = max(
                0,
                $callOffAvailable
                    - $effectiveAvailable
            );

            $balances[$itemId] = [
                'province_distribution_item_id' => $allocationItemId,

                'item_id' => $itemId,

                'item' => $allocationItem->item,

                'allocated' => $allocated,

                'actual_received' => $actualReceived,

                'previously_distributed' => $previouslyDistributed,

                'remaining_receivable' => $remainingReceivable,

                /*
                 * Raw remaining balance under the selected Call-Off.
                 */
                'call_off_available' => $callOffAvailable,

                /*
                 * Current province-wide stock.
                 */
                'pooled_available' => $pooledAvailable,

                /*
                 * Quantity used by:
                 * - the Blade max attribute
                 * - JavaScript validation
                 * - Form Request validation
                 * - SupplyDesignationService validation
                 */
                'available_for_projects' => $effectiveAvailable,

                'legacy_or_unassigned_reserve' => $legacyOrUnassignedReserve,
            ];
        }

        return $balances;
    }

    /**
     * Get the safe available quantity for one PPE item.
     */
    public function availableQuantity(
        ProvinceDistribution $allocation,
        int $itemId
    ): int {
        $balances = $this->balances(
            $allocation
        );

        return (int) (
            $balances[$itemId][
                'available_for_projects'
            ] ?? 0
        );
    }

    /**
     * Validate submitted project quantities.
     *
     * @param  array<int|string, mixed>  $submittedItems
     */
    public function validateProjectQuantities(
        ProvinceDistribution $allocation,
        array $submittedItems
    ): void {
        $balances = $this->balances(
            $allocation
        );

        $errors = [];

        $positiveTotal = 0;

        foreach (
            $submittedItems as $itemId => $quantity
        ) {
            if (
                filter_var(
                    $itemId,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                $errors['items'] =
                    'One submitted PPE item identifier is invalid.';

                continue;
            }

            $itemId = (int) $itemId;

            if (
                filter_var(
                    $quantity,
                    FILTER_VALIDATE_INT
                ) === false
            ) {
                $errors[
                    "items.{$itemId}"
                ] =
                    'The project quantity must be a whole number.';

                continue;
            }

            $quantity = (int) $quantity;

            if ($quantity < 0) {
                $errors[
                    "items.{$itemId}"
                ] =
                    'Project quantities cannot be negative.';

                continue;
            }

            if ($quantity === 0) {
                continue;
            }

            $positiveTotal += $quantity;

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
                $item = $balances[
                    $itemId
                ]['item'];

                $itemName = $this->displayItemName(
                    $item
                );

                $callOffAvailable = (int) $balances[
                    $itemId
                ]['call_off_available'];

                $pooledAvailable = (int) $balances[
                    $itemId
                ]['pooled_available'];

                $errors[
                    "items.{$itemId}"
                ] =
                    "{$itemName} has only "
                    .number_format($available)
                    .' safely available. '
                    .'The selected Call-Off has '
                    .number_format($callOffAvailable)
                    .' remaining, while the current combined provincial '
                    .'inventory has '
                    .number_format($pooledAvailable)
                    .'.';
            }
        }

        if ($positiveTotal <= 0) {
            $errors['items'] =
                'Enter at least one PPE quantity greater than zero.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    /**
     * Calculate received quantities grouped by original provincial
     * allocation item.
     *
     * @param  array<int, int>  $allocationItemIds
     * @return Collection<int|string, int>
     */
    private function receivedByAllocationItem(
        ProvinceDistribution $allocation,
        array $allocationItemIds
    ): Collection {
        if ($allocationItemIds === []) {
            return collect();
        }

        return DeliveryReceiptItem::query()
            ->whereIn(
                'province_distribution_item_id',
                $allocationItemIds
            )
            ->whereHas(
                'deliveryReceipt',
                function ($query) use (
                    $allocation
                ): void {
                    $query->where(
                        'province_distribution_id',
                        $allocation->id
                    );
                }
            )
            ->selectRaw(
                '
                province_distribution_item_id,
                SUM(received_quantity) AS total_received
                '
            )
            ->groupBy(
                'province_distribution_item_id'
            )
            ->pluck(
                'total_received',
                'province_distribution_item_id'
            )
            ->map(
                fn ($quantity): int => (int) $quantity
            );
    }

    /**
     * Calculate completed project distributions linked to the selected
     * Call-Off.
     *
     * @param  array<int, int>  $itemIds
     * @return Collection<int|string, int>
     */
    private function distributedByItem(
        ProvinceDistribution $allocation,
        array $itemIds
    ): Collection {
        if ($itemIds === []) {
            return collect();
        }

        return SupplyDesignationItem::query()
            ->whereIn(
                'item_id',
                $itemIds
            )
            ->whereHas(
                'supplyDesignation',
                function ($query) use (
                    $allocation
                ): void {
                    $query
                        ->where(
                            'province_distribution_id',
                            $allocation->id
                        )
                        ->where(
                            'status',
                            'Completed'
                        );
                }
            )
            ->selectRaw(
                '
                item_id,
                SUM(quantity) AS total_distributed
                '
            )
            ->groupBy('item_id')
            ->pluck(
                'total_distributed',
                'item_id'
            )
            ->map(
                fn ($quantity): int => (int) $quantity
            );
    }

    /**
     * Ensure the authenticated Provincial Office can access the
     * selected allocation.
     */
    private function ensureProvinceAccess(
        ProvinceDistribution $allocation
    ): void {
        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        abort_unless(
            (int) $allocation->province_id
                === (int) $provinceId,
            403,
            'You cannot use another province’s Call-Off allocation.'
        );
    }

    /**
     * Build a readable PPE name.
     */
    private function displayItemName(
        mixed $item
    ): string {
        if (! $item) {
            return 'PPE item';
        }

        $itemName = trim(
            (string) (
                $item->item_name
                ?? 'PPE item'
            )
        );

        $label = trim(
            (string) (
                $item->label
                ?? ''
            )
        );

        return $label !== ''
            ? "{$itemName} ({$label})"
            : $itemName;
    }
}
