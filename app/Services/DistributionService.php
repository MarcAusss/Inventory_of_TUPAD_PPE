<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Province;
use App\Models\ProvinceDistributionItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\TSSDDistribution;
use App\Models\TssdDistributionBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DistributionService
{
    /**
     * Maps the form field names to the system PPE records.
     *
     * @var array<string, array{name: string, label: string|null}>
     */
    private array $ppeMap = [
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
     * Create a normalized TSSD distribution batch.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function createBatch(
        array $data
    ): TssdDistributionBatch {
        $user = Auth::user();

        abort_unless(
            $user
            && $user->isTssd(),
            403,
            'Only the TSSD Unit may create distributions.'
        );

        return DB::transaction(
            function () use ($data, $user): TssdDistributionBatch {
                /*
                 * Lock the Purchase Order itself so another TSSD request
                 * cannot allocate from the same PO simultaneously.
                 */
                $purchaseOrder = PurchaseOrder::query()
                    ->with([
                        'items.item',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        (int) $data['purchase_order_id']
                    );

                if (
                    !in_array(
                        $purchaseOrder->status,
                        [
                            'Pending Distribution',
                            'Distributed',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => 'This Purchase Order is no longer available for distribution.',
                    ]);
                }

                $distributions =
                    $this->normalizeDistributions(
                        $data['distributions'] ?? []
                    );

                if ($distributions === []) {
                    throw ValidationException::withMessages([
                        'distributions' => 'Assign PPE to at least one province.',
                    ]);
                }

                $items = $this->resolvePpeItems();

                /*
                 * Lock the Purchase Order item rows so their purchased
                 * quantities remain stable during validation.
                 */
                $purchaseOrderItems =
                    PurchaseOrderItem::query()
                        ->with('item')
                        ->where(
                            'purchase_order_id',
                            $purchaseOrder->id
                        )
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('item_id');

                $requestedByItem =
                    $this->calculateRequestedTotals(
                        $distributions,
                        $items
                    );

                $remainingByItem =
                    $this->calculateRemainingByItem(
                        $purchaseOrder,
                        $purchaseOrderItems
                    );

                $this->validateRequestedTotals(
                    $requestedByItem,
                    $remainingByItem,
                    $items
                );

                $batch =
                    TssdDistributionBatch::create([
                        'purchase_order_id' => $purchaseOrder->id,

                        'created_by' => $user->id,

                        'distribution_date' => now()->toDateString(),

                        'status' => 'Submitted',

                        'remarks' => $data['remarks']
                            ?? null,
                    ]);

                foreach (
                    $distributions as $distributionIndex => $distribution
                ) {
                    $province = Province::query()
                        ->findOrFail(
                            $distribution[
                                'province_id'
                            ]
                        );

                    $provinceDistribution =
                        $batch
                            ->provinceDistributions()
                            ->create([
                                'province_id' => $province->id,

                                /*
                                |--------------------------------------------------------------------------
                                | Every province has its own delivery schedule
                                |--------------------------------------------------------------------------
                                */
                                'scheduled_delivery_date' =>
                                    $distribution['scheduled_delivery_date'],

                                /*
                                |--------------------------------------------------------------------------
                                | Snapshot the Provincial Office address.
                                |--------------------------------------------------------------------------
                                */
                                'place_of_delivery' =>
                                    $province->deliveryLocation(),

                                'status' => 'Pending',

                                'remarks' =>
                                    $distribution['remarks']
                                    ?? null,
                            ]);

                    $hasPositiveItem = false;

                    foreach (
                        $this->ppeMap as $field => $ppeDefinition
                    ) {
                        $quantity = (int) (
                            $distribution[$field]
                            ?? 0
                        );

                        if ($quantity <= 0) {
                            continue;
                        }

                        $hasPositiveItem = true;

                        $item = $items[$field];

                        $provinceDistribution
                            ->items()
                            ->create([
                                'item_id' => $item->id,

                                'quantity' => $quantity,
                            ]);
                    }

                    if (!$hasPositiveItem) {
                        throw ValidationException::withMessages([
                            "distributions.{$distributionIndex}" => "Enter at least one PPE quantity for {$province->name}.",
                        ]);
                    }
                }

                /*
                 * Mark the PO as distributed once at least one batch exists.
                 * It can still have remaining stock for another batch.
                 */
                $purchaseOrder->update([
                    'status' => 'Distributed',
                ]);

                return $batch->fresh([
                    'purchaseOrder.supplier',
                    'creator',
                    'provinceDistributions.province',
                    'provinceDistributions.items.item',
                ]);
            },
            attempts: 3
        );
    }

    /**
     * Normalize and sanitize submitted distribution entries.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeDistributions(
        mixed $distributions
    ): array {
        if (!is_array($distributions)) {
            throw ValidationException::withMessages([
                'distributions' => 'The submitted distribution data is invalid.',
            ]);
        }

        $normalized = [];

        foreach (
            $distributions as $index => $distribution
        ) {
            if (!is_array($distribution)) {
                throw ValidationException::withMessages([
                    "distributions.{$index}" => 'This province distribution entry is invalid.',
                ]);
            }

            $provinceId = filter_var(
                $distribution['province_id']
                ?? null,
                FILTER_VALIDATE_INT
            );

            if ($provinceId === false) {
                throw ValidationException::withMessages([
                    "distributions.{$index}.province_id" => 'Select a valid province.',
                ]);
            }

            $scheduledDeliveryDate = trim(
                (string) (
                    $distribution[
                        'scheduled_delivery_date'
                    ] ?? ''
                )
            );

            if ($scheduledDeliveryDate === '') {
                throw ValidationException::withMessages([
                    "distributions.{$index}.scheduled_delivery_date" =>
                        'Every province must have its own delivery date.',
                ]);
            }

            $entry = [
                'province_id' => (int) $provinceId,

                'scheduled_delivery_date' =>
                    $scheduledDeliveryDate,

                'remarks' =>
                    isset($distribution['remarks'])
                    ? trim(
                        (string) $distribution['remarks']
                    )
                    : null,
            ];

            foreach (
                array_keys($this->ppeMap) as $field
            ) {
                $quantity =
                    $distribution[$field]
                    ?? 0;

                if (
                    $quantity === null
                    || $quantity === ''
                ) {
                    $quantity = 0;
                }

                if (
                    filter_var(
                        $quantity,
                        FILTER_VALIDATE_INT
                    ) === false
                ) {
                    throw ValidationException::withMessages([
                        "distributions.{$index}.{$field}" => 'The PPE quantity must be a whole number.',
                    ]);
                }

                $quantity = (int) $quantity;

                if ($quantity < 0) {
                    throw ValidationException::withMessages([
                        "distributions.{$index}.{$field}" => 'PPE quantities cannot be negative.',
                    ]);
                }

                $entry[$field] = $quantity;
            }

            $normalized[] = $entry;
        }

        $provinceIds = collect($normalized)
            ->pluck('province_id');

        if (
            $provinceIds->duplicates()
                ->isNotEmpty()
        ) {
            throw ValidationException::withMessages([
                'distributions' => 'A province cannot appear more than once in the same distribution batch.',
            ]);
        }

        return $normalized;
    }

    /**
     * Resolve the seven system PPE records.
     *
     * @return array<string, Item>
     */
    private function resolvePpeItems(): array
    {
        $items = [];

        foreach (
            $this->ppeMap as $field => $definition
        ) {
            $query = Item::query()
                ->where(
                    'item_name',
                    $definition['name']
                );

            if ($definition['label'] === null) {
                $query->whereNull('label');
            } else {
                $query->where(
                    'label',
                    $definition['label']
                );
            }

            $item = $query->first();

            if (!$item) {
                $displayName =
                    $definition['label']
                    ? "{$definition['name']} ({$definition['label']})"
                    : $definition['name'];

                throw ValidationException::withMessages([
                    'distributions' => "{$displayName} is missing from the PPE items table.",
                ]);
            }

            $items[$field] = $item;
        }

        return $items;
    }

    /**
     * Calculate total quantity requested across all selected provinces.
     *
     * @param  array<int, array<string, mixed>>  $distributions
     * @param  array<string, Item>  $items
     * @return array<int, int>
     */
    private function calculateRequestedTotals(
        array $distributions,
        array $items
    ): array {
        $totals = [];

        foreach ($items as $item) {
            $totals[$item->id] = 0;
        }

        foreach (
            $distributions as $distribution
        ) {
            foreach (
                $this->ppeMap as $field => $definition
            ) {
                $item = $items[$field];

                $totals[$item->id] +=
                    (int) (
                        $distribution[$field]
                        ?? 0
                    );
            }
        }

        return $totals;
    }

    /**
     * Calculate PO remaining quantities after all existing distributions.
     *
     * This counts both the legacy table and the normalized batch tables
     * while the legacy workflow is still present.
     *
     * @param  Collection<int, PurchaseOrderItem>  $purchaseOrderItems
     * @return array<int, int>
     */
    private function calculateRemainingByItem(
        PurchaseOrder $purchaseOrder,
        $purchaseOrderItems
    ): array {
        $remaining = [];

        foreach (
            $purchaseOrderItems as $purchaseOrderItem
        ) {
            $remaining[
                $purchaseOrderItem->item_id
            ] = (int) $purchaseOrderItem->quantity;
        }

        /*
         * Legacy distribution totals.
         */
        $legacyTotals = TSSDDistribution::query()
            ->where(
                'purchase_order_id',
                $purchaseOrder->id
            )
            ->selectRaw(
                'item_id, SUM(quantity) as total_quantity'
            )
            ->groupBy('item_id')
            ->pluck(
                'total_quantity',
                'item_id'
            );

        /*
         * Normalized distribution totals, excluding cancelled batches.
         */
        $normalizedTotals =
            ProvinceDistributionItem::query()
                ->whereHas(
                    'provinceDistribution.distributionBatch',
                    function ($query) use ($purchaseOrder): void {
                        $query
                            ->where(
                                'purchase_order_id',
                                $purchaseOrder->id
                            )
                            ->where(
                                'status',
                                '!=',
                                'Cancelled'
                            );
                    }
                )
                ->selectRaw(
                    'item_id, SUM(quantity) as total_quantity'
                )
                ->groupBy('item_id')
                ->pluck(
                    'total_quantity',
                    'item_id'
                );

        foreach (
            array_keys($remaining) as $itemId
        ) {
            $used =
                (int) (
                    $legacyTotals[$itemId]
                    ?? 0
                )
                + (int) (
                    $normalizedTotals[$itemId]
                    ?? 0
                );

            $remaining[$itemId] =
                max(
                    0,
                    $remaining[$itemId]
                    - $used
                );
        }

        return $remaining;
    }

    /**
     * Reject a combined allocation that exceeds remaining PO quantities.
     *
     * @param  array<int, int>  $requestedByItem
     * @param  array<int, int>  $remainingByItem
     * @param  array<string, Item>  $items
     */
    private function validateRequestedTotals(
        array $requestedByItem,
        array $remainingByItem,
        array $items
    ): void {
        $errors = [];

        foreach (
            $items as $field => $item
        ) {
            $requested =
                $requestedByItem[$item->id]
                ?? 0;

            $remaining =
                $remainingByItem[$item->id]
                ?? 0;

            if ($requested <= $remaining) {
                continue;
            }

            $displayName =
                $item->label
                ? "{$item->item_name} ({$item->label})"
                : $item->item_name;

            $errors[
                "totals.{$field}"
            ] = "{$displayName} has "
                . number_format($remaining)
                . ' remaining in this Purchase Order, but '
                . number_format($requested)
                . ' was allocated across all provinces.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }
}
