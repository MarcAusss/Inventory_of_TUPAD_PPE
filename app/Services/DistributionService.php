<?php

namespace App\Services;

use App\Models\Province;
use App\Models\ProvinceDistributionItem;
use App\Models\PurchaseOrder;
use App\Models\TSSDDistribution;
use App\Models\TssdDistributionBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DistributionService extends BaseService
{
    /**
     * Maps the submitted Blade/JavaScript fields to the PPE records stored in
     * the items table.
     *
     * @var array<string, array{name: string, label: string|null}>
     */
    private const PPE_MAP = [
        'long_sleeve_medium' => [
            'name' => 'Long Sleeve',
            'label' => 'Medium',
        ],

        'long_sleeve_large' => [
            'name' => 'Long Sleeve',
            'label' => 'Large',
        ],

        'bucket_hat' => [
            'name' => 'Bucket Hat',
            'label' => null,
        ],

        'rubber_boots_us9' => [
            'name' => 'Rubber Boots',
            'label' => 'US9',
        ],

        'rubber_boots_us10' => [
            'name' => 'Rubber Boots',
            'label' => 'US10',
        ],

        'hand_gloves' => [
            'name' => 'Hand Gloves',
            'label' => null,
        ],

        'mask' => [
            'name' => 'Mask',
            'label' => null,
        ],
    ];

    /**
     * Create a complete TSSD distribution batch.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBatch(array $data): TssdDistributionBatch
    {
        $this->requireTssd();

        return DB::transaction(function () use ($data): TssdDistributionBatch {
            /*
             * Lock the Purchase Order while calculating and saving the
             * distribution. This reduces the chance of simultaneous requests
             * allocating the same remaining quantities.
             */
            $purchaseOrder = PurchaseOrder::query()
                ->with([
                    'items.item',
                ])
                ->lockForUpdate()
                ->findOrFail($data['purchase_order_id']);

            $purchaseOrderItems = $purchaseOrder->items;

            if ($purchaseOrderItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'The selected Purchase Order does not contain PPE items.',
                ]);
            }

            $itemIdsByField = $this->resolvePurchaseOrderItemIds(
                $purchaseOrderItems
            );

            $purchasedQuantities = $this->purchasedQuantities(
                $purchaseOrderItems
            );

            /*
             * Include both the legacy tssd_distributions table and the new
             * normalized province_distribution_items table while the old
             * workflow is still being migrated.
             */
            $alreadyDistributed = $this->alreadyDistributedQuantities(
                $purchaseOrder->id
            );

            $requestedQuantities = $this->requestedQuantities(
                $data['distributions'],
                $itemIdsByField
            );

            $this->validateAvailableQuantities(
                $purchasedQuantities,
                $alreadyDistributed,
                $requestedQuantities,
                $purchaseOrderItems
            );

            $provinces = Province::query()
                ->whereIn(
                    'id',
                    collect($data['distributions'])
                        ->pluck('province_id')
                        ->map(fn ($id): int => (int) $id)
                        ->all()
                )
                ->get()
                ->keyBy('id');

            if ($provinces->count() !== count($data['distributions'])) {
                throw ValidationException::withMessages([
                    'distributions' => 'One or more selected provinces could not be loaded.',
                ]);
            }

            $batch = TssdDistributionBatch::create([
                'purchase_order_id' => $purchaseOrder->id,
                'created_by' => $this->userId(),
                'distribution_date' => now()->toDateString(),
                'status' => 'Submitted',
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['distributions'] as $distributionData) {
                $provinceId = (int) $distributionData['province_id'];
                $province = $provinces->get($provinceId);

                $provinceDistribution = $batch
                    ->provinceDistributions()
                    ->create([
                        'province_id' => $provinceId,
                        'scheduled_delivery_date' => $data['delivery_date'],
                        'place_of_delivery' => $province->deliveryLocation(),
                        'status' => 'Pending',
                        'remarks' => null,
                    ]);

                foreach (self::PPE_MAP as $field => $definition) {
                    $quantity = (int) (
                        $distributionData[$field] ?? 0
                    );

                    if ($quantity <= 0) {
                        continue;
                    }

                    $itemId = $itemIdsByField[$field] ?? null;

                    if (! $itemId) {
                        throw ValidationException::withMessages([
                            "distributions.{$field}" => $this->ppeDisplayName($definition)
                                .' does not exist in the selected Purchase Order.',
                        ]);
                    }

                    $provinceDistribution->items()->create([
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $this->updatePurchaseOrderStatus(
                $purchaseOrder,
                $purchasedQuantities,
                $alreadyDistributed,
                $requestedQuantities
            );

            return $batch->load([
                'purchaseOrder.supplier',
                'creator',
                'provinceDistributions.province',
                'provinceDistributions.items.item',
            ]);
        });
    }

    /**
     * Determine which Item ID corresponds to every supported PPE input field.
     *
     * @param  Collection<int, mixed>  $purchaseOrderItems
     * @return array<string, int>
     */
    private function resolvePurchaseOrderItemIds(
        Collection $purchaseOrderItems
    ): array {
        $resolved = [];

        foreach (self::PPE_MAP as $field => $definition) {
            $purchaseOrderItem = $purchaseOrderItems->first(
                function ($purchaseOrderItem) use ($definition): bool {
                    $item = $purchaseOrderItem->item;

                    if (! $item) {
                        return false;
                    }

                    if ($item->item_name !== $definition['name']) {
                        return false;
                    }

                    if ($definition['label'] === null) {
                        return true;
                    }

                    return $item->label === $definition['label'];
                }
            );

            if ($purchaseOrderItem) {
                $resolved[$field] = (int) $purchaseOrderItem->item_id;
            }
        }

        return $resolved;
    }

    /**
     * Purchase Order quantities grouped by Item ID.
     *
     * @param  Collection<int, mixed>  $purchaseOrderItems
     * @return array<int, int>
     */
    private function purchasedQuantities(
        Collection $purchaseOrderItems
    ): array {
        return $purchaseOrderItems
            ->groupBy('item_id')
            ->map(
                fn (Collection $items): int => (int) $items->sum('quantity')
            )
            ->mapWithKeys(
                fn (int $quantity, int|string $itemId): array => [(int) $itemId => $quantity]
            )
            ->all();
    }

    /**
     * Calculate quantities that were already distributed through both the
     * legacy and normalized distribution structures.
     *
     * @return array<int, int>
     */
    private function alreadyDistributedQuantities(
        int $purchaseOrderId
    ): array {
        $legacy = TSSDDistribution::query()
            ->where('purchase_order_id', $purchaseOrderId)
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id')
            ->map(
                fn ($quantity): int => (int) $quantity
            );

        $normalized = ProvinceDistributionItem::query()
            ->whereHas(
                'provinceDistribution.distributionBatch',
                function ($query) use ($purchaseOrderId): void {
                    $query
                        ->where(
                            'purchase_order_id',
                            $purchaseOrderId
                        )
                        ->where('status', '!=', 'Cancelled');
                }
            )
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id')
            ->map(
                fn ($quantity): int => (int) $quantity
            );

        return $legacy
            ->mergeRecursive($normalized)
            ->map(function ($quantity): int {
                if (is_array($quantity)) {
                    return array_sum(
                        array_map('intval', $quantity)
                    );
                }

                return (int) $quantity;
            })
            ->mapWithKeys(
                fn (int $quantity, int|string $itemId): array => [(int) $itemId => $quantity]
            )
            ->all();
    }

    /**
     * Calculate the quantities submitted in the current request.
     *
     * @param  array<int, array<string, mixed>>  $distributions
     * @param  array<string, int>  $itemIdsByField
     * @return array<int, int>
     */
    private function requestedQuantities(
        array $distributions,
        array $itemIdsByField
    ): array {
        $requested = [];

        foreach ($distributions as $distribution) {
            foreach (self::PPE_MAP as $field => $definition) {
                $quantity = (int) ($distribution[$field] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $itemId = $itemIdsByField[$field] ?? null;

                if (! $itemId) {
                    throw ValidationException::withMessages([
                        "distributions.{$field}" => $this->ppeDisplayName($definition)
                            .' is not available in the selected Purchase Order.',
                    ]);
                }

                $requested[$itemId] =
                    ($requested[$itemId] ?? 0) + $quantity;
            }
        }

        return $requested;
    }

    /**
     * Ensure requested allocation totals do not exceed the available Purchase
     * Order quantities.
     *
     * @param  array<int, int>  $purchased
     * @param  array<int, int>  $alreadyDistributed
     * @param  array<int, int>  $requested
     * @param  Collection<int, mixed>  $purchaseOrderItems
     */
    private function validateAvailableQuantities(
        array $purchased,
        array $alreadyDistributed,
        array $requested,
        Collection $purchaseOrderItems
    ): void {
        $errors = [];

        foreach ($requested as $itemId => $requestedQuantity) {
            $purchasedQuantity = $purchased[$itemId] ?? 0;

            $previouslyDistributed =
                $alreadyDistributed[$itemId] ?? 0;

            $availableQuantity =
                $purchasedQuantity - $previouslyDistributed;

            if ($requestedQuantity <= $availableQuantity) {
                continue;
            }

            $purchaseOrderItem = $purchaseOrderItems
                ->firstWhere('item_id', $itemId);

            $itemName = $purchaseOrderItem?->item?->item_name
                ?? 'PPE item';

            $label = $purchaseOrderItem?->item?->label;

            $displayName = $label
                ? "{$itemName} ({$label})"
                : $itemName;

            $errors["item_{$itemId}"] =
                "{$displayName} has only {$availableQuantity} remaining, "
                ."but {$requestedQuantity} was requested.";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Update the Purchase Order workflow status after allocation.
     *
     * @param  array<int, int>  $purchased
     * @param  array<int, int>  $alreadyDistributed
     * @param  array<int, int>  $requested
     */
    private function updatePurchaseOrderStatus(
        PurchaseOrder $purchaseOrder,
        array $purchased,
        array $alreadyDistributed,
        array $requested
    ): void {
        $fullyDistributed = true;

        foreach ($purchased as $itemId => $purchasedQuantity) {
            $totalDistributed =
                ($alreadyDistributed[$itemId] ?? 0)
                + ($requested[$itemId] ?? 0);

            if ($totalDistributed < $purchasedQuantity) {
                $fullyDistributed = false;
                break;
            }
        }

        $purchaseOrder->update([
            'status' => $fullyDistributed
                ? 'Distributed'
                : 'Pending Distribution',
        ]);
    }

    /**
     * Convert an internal PPE definition to a readable validation name.
     *
     * @param  array{name: string, label: string|null}  $definition
     */
    private function ppeDisplayName(array $definition): string
    {
        return $definition['label']
            ? "{$definition['name']} ({$definition['label']})"
            : $definition['name'];
    }
}
