<?php

namespace App\Services;

use App\Models\DeliveryReceiptItem;
use App\Models\ProvinceDistribution;
use App\Models\SupplyDesignationItem;
use Illuminate\Support\Collection;

class CallOffInventoryService
{
    /**
     * Build the inventory balances for one provincial allocation.
     *
     * Returned structure:
     *
     * [
     *     item_id => [
     *         'item_id' => int,
     *         'allocated_quantity' => int,
     *         'actual_received' => int,
     *         'previously_distributed' => int,
     *         'call_off_available' => int,
     *         'available_for_projects' => int,
     *     ],
     * ]
     *
     * @return array<int, array<string, int>>
     */
    public function balances(
        ProvinceDistribution $allocation
    ): array {
        $allocation->loadMissing([
            'items.item',
            'deliveryReceipts.items',
            'supplyDesignations.items',
        ]);

        $allocated = $this->allocatedQuantities($allocation);
        $received = $this->receivedQuantities($allocation);
        $distributed = $this->distributedQuantities($allocation);

        $itemIds = collect()
            ->merge($allocated->keys())
            ->merge($received->keys())
            ->merge($distributed->keys())
            ->filter()
            ->unique()
            ->values();

        return $itemIds
            ->mapWithKeys(function (int|string $itemId) use (
                $allocated,
                $received,
                $distributed
            ): array {
                $itemId = (int) $itemId;

                $allocatedQuantity = max(
                    0,
                    (int) $allocated->get($itemId, 0)
                );

                $actualReceived = max(
                    0,
                    (int) $received->get($itemId, 0)
                );

                $previouslyDistributed = max(
                    0,
                    (int) $distributed->get($itemId, 0)
                );

                /*
                 * PPE remaining under the approved Call-Off allocation.
                 *
                 * This protects against receiving more than what was
                 * originally allocated.
                 */
                $callOffAvailable = max(
                    0,
                    $allocatedQuantity - $previouslyDistributed
                );

                /*
                 * PPE that may still be assigned to projects.
                 *
                 * This is based on what was actually received, less the
                 * quantities already issued through Supply Designations.
                 */
                $receivedAvailable = max(
                    0,
                    $actualReceived - $previouslyDistributed
                );

                /*
                 * Use the lower balance so the office cannot distribute:
                 *
                 * 1. More than it actually received; or
                 * 2. More than the approved Call-Off allocation.
                 */
                $availableForProjects = min(
                    $callOffAvailable,
                    $receivedAvailable
                );

                return [
                    $itemId => [
                        'item_id' => $itemId,
                        'allocated_quantity' => $allocatedQuantity,
                        'actual_received' => $actualReceived,
                        'previously_distributed' => $previouslyDistributed,
                        'call_off_available' => $callOffAvailable,
                        'available_for_projects' => max(
                            0,
                            $availableForProjects
                        ),
                    ],
                ];
            })
            ->all();
    }

    /**
     * Determine whether an allocation has any PPE available
     * for project distribution.
     */
    public function hasAvailableStock(
        ProvinceDistribution $allocation
    ): bool {
        return collect($this->balances($allocation))
            ->contains(
                fn (array $balance): bool =>
                    (int) ($balance['available_for_projects'] ?? 0) > 0
            );
    }

    /**
     * Get the total quantity currently available for projects.
     */
    public function totalAvailableForProjects(
        ProvinceDistribution $allocation
    ): int {
        return collect($this->balances($allocation))
            ->sum(
                fn (array $balance): int =>
                    (int) ($balance['available_for_projects'] ?? 0)
            );
    }

    /**
     * Get the available quantity of one item.
     */
    public function availableQuantity(
        ProvinceDistribution $allocation,
        int $itemId
    ): int {
        $balance = $this->balances($allocation)[$itemId] ?? null;

        return max(
            0,
            (int) ($balance['available_for_projects'] ?? 0)
        );
    }

    /**
     * Get the total quantity allocated under the Call-Off.
     */
    public function totalAllocated(
        ProvinceDistribution $allocation
    ): int {
        return collect($this->balances($allocation))
            ->sum(
                fn (array $balance): int =>
                    (int) ($balance['allocated_quantity'] ?? 0)
            );
    }

    /**
     * Get the total quantity actually received.
     */
    public function totalReceived(
        ProvinceDistribution $allocation
    ): int {
        return collect($this->balances($allocation))
            ->sum(
                fn (array $balance): int =>
                    (int) ($balance['actual_received'] ?? 0)
            );
    }

    /**
     * Get the total quantity already assigned to projects.
     */
    public function totalDistributed(
        ProvinceDistribution $allocation
    ): int {
        return collect($this->balances($allocation))
            ->sum(
                fn (array $balance): int =>
                    (int) ($balance['previously_distributed'] ?? 0)
            );
    }

    /**
     * Quantities allocated by TSSD to the province.
     *
     * @return Collection<int, int>
     */
    private function allocatedQuantities(
        ProvinceDistribution $allocation
    ): Collection {
        if ($allocation->relationLoaded('items')) {
            return $allocation->items
                ->groupBy('item_id')
                ->map(
                    fn (Collection $rows): int =>
                        (int) $rows->sum('quantity')
                );
        }

        return $allocation->items()
            ->selectRaw('item_id, SUM(quantity) AS total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id')
            ->map(
                fn (mixed $quantity): int => (int) $quantity
            );
    }

    /**
     * Quantities received through Delivery Receipts.
     *
     * Only Delivery Receipts with the status "Received" are included.
     *
     * @return Collection<int, int>
     */
    private function receivedQuantities(
        ProvinceDistribution $allocation
    ): Collection {
        if ($allocation->relationLoaded('deliveryReceipts')) {
            return $allocation->deliveryReceipts
                ->filter(
                    fn ($receipt): bool =>
                        strcasecmp(
                            trim((string) $receipt->status),
                            'Received'
                        ) === 0
                )
                ->flatMap(
                    fn ($receipt) => $receipt->items
                )
                ->groupBy('item_id')
                ->map(
                    fn (Collection $rows): int =>
                        (int) $rows->sum('received_quantity')
                );
        }

        return DeliveryReceiptItem::query()
            ->selectRaw(
                'delivery_receipt_items.item_id,
                SUM(delivery_receipt_items.received_quantity)
                AS total_quantity'
            )
            ->join(
                'delivery_receipts',
                'delivery_receipts.id',
                '=',
                'delivery_receipt_items.delivery_receipt_id'
            )
            ->where(
                'delivery_receipts.province_distribution_id',
                $allocation->id
            )
            ->where('delivery_receipts.status', 'Received')
            ->groupBy('delivery_receipt_items.item_id')
            ->pluck(
                'total_quantity',
                'delivery_receipt_items.item_id'
            )
            ->map(
                fn (mixed $quantity): int => (int) $quantity
            );
    }

    /**
     * Quantities already issued through Supply Designations.
     *
     * @return Collection<int, int>
     */
    private function distributedQuantities(
        ProvinceDistribution $allocation
    ): Collection {
        if ($allocation->relationLoaded('supplyDesignations')) {
            return $allocation->supplyDesignations
                ->flatMap(
                    fn ($designation) => $designation->items
                )
                ->groupBy('item_id')
                ->map(
                    fn (Collection $rows): int =>
                        (int) $rows->sum('quantity')
                );
        }

        return SupplyDesignationItem::query()
            ->selectRaw(
                'supply_designation_items.item_id,
                SUM(supply_designation_items.quantity)
                AS total_quantity'
            )
            ->join(
                'supply_designations',
                'supply_designations.id',
                '=',
                'supply_designation_items.supply_designation_id'
            )
            ->where(
                'supply_designations.province_distribution_id',
                $allocation->id
            )
            ->groupBy('supply_designation_items.item_id')
            ->pluck(
                'total_quantity',
                'supply_designation_items.item_id'
            )
            ->map(
                fn (mixed $quantity): int => (int) $quantity
            );
    }
}