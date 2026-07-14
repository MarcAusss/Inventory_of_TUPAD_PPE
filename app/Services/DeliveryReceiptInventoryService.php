<?php

namespace App\Services;

use App\Models\DeliveryReceipt;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignationItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DeliveryReceiptInventoryService extends BaseService
{
    /**
     * Return received Delivery Receipts belonging to the authenticated
     * Provincial Office that still have project-usable PPE.
     *
     * Each Delivery Receipt remains an independent stock source.
     *
     * @return Collection<int, DeliveryReceipt>
     */
    public function availableReceipts(): Collection
    {
        $this->requireProvincial();

        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        return DeliveryReceipt::query()
            ->with([
                'items.item',
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->where('province_id', $provinceId)
            ->where('status', 'Received')
            ->whereNotNull('province_distribution_id')
            ->whereHas(
                'provinceDistribution.distributionBatch.callOff',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        'Approved',
                        'Completed',
                    ]
                )
            )
            ->orderByDesc('delivery_date')
            ->orderByDesc('id')
            ->get()
            ->map(
                function (
                    DeliveryReceipt $receipt
                ): DeliveryReceipt {
                    $balances = $this->balances(
                        $receipt
                    );

                    $receipt->setAttribute(
                        'project_balances',
                        $balances
                    );

                    $receipt->setAttribute(
                        'available_for_projects_total',
                        (int) collect($balances)
                            ->sum('available_for_projects')
                    );

                    return $receipt;
                }
            )
            ->filter(
                fn (
                    DeliveryReceipt $receipt
                ): bool => (int) $receipt
                    ->available_for_projects_total > 0
            )
            ->values();
    }

    /**
     * Build PPE balances for one Delivery Receipt.
     *
     * Actual Received:
     * Quantity physically received under this exact Delivery Receipt.
     *
     * Previously Distributed:
     * Completed project quantities linked to this exact Delivery Receipt.
     *
     * DR Remaining:
     * Actual Received minus Previously Distributed.
     *
     * Safe Available:
     * The lower of DR Remaining and current pooled provincial stock.
     *
     * @return array<int, array<string, mixed>>
     */
    public function balances(
        DeliveryReceipt $receipt
    ): array {
        $this->ensureProvinceAccess(
            $receipt
        );

        $receipt->loadMissing([
            'items.item',
            'provinceDistribution.distributionBatch.callOff',
            'provinceDistribution.distributionBatch.purchaseOrder.supplier',
        ]);

        abort_unless(
            $receipt->status === 'Received',
            422,
            'Only a received Delivery Receipt can be used for a project designation.'
        );

        abort_unless(
            $receipt->province_distribution_id,
            422,
            'The Delivery Receipt is not linked to a Call-Off allocation.'
        );

        $itemIds = $receipt->items
            ->pluck('item_id')
            ->map(
                fn ($itemId): int => (int) $itemId
            )
            ->unique()
            ->values()
            ->all();

        $receivedByItem = $receipt->items
            ->groupBy('item_id')
            ->map(
                fn (
                    Collection $items
                ): int => (int) $items->sum(
                    fn ($item): int => (int) (
                        $item->received_quantity
                        ?? $item->quantity
                        ?? 0
                    )
                )
            );

        $distributedByItem =
            $this->distributedByItem(
                $receipt,
                $itemIds
            );

        $pooledInventoryByItem =
            ProvincialInventory::query()
                ->where(
                    'province_id',
                    $receipt->province_id
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
            $receipt->items
                ->unique('item_id')
                ->values() as $receiptItem
        ) {
            $itemId = (int) $receiptItem->item_id;

            $actualReceived = (int) (
                $receivedByItem[$itemId]
                ?? 0
            );

            $previouslyDistributed = (int) (
                $distributedByItem[$itemId]
                ?? 0
            );

            $deliveryReceiptRemaining = max(
                0,
                $actualReceived
                    - $previouslyDistributed
            );

            $pooledAvailable = max(
                0,
                (int) (
                    $pooledInventoryByItem[$itemId]
                    ?? 0
                )
            );

            /*
             * This does not combine other Delivery Receipts.
             *
             * The pooled value is only a final safety cap to avoid
             * deducting stock that no longer physically exists.
             */
            $availableForProjects = min(
                $deliveryReceiptRemaining,
                $pooledAvailable
            );

            $balances[$itemId] = [
                'item_id' => $itemId,

                'item' => $receiptItem->item,

                'actual_received' => $actualReceived,

                'previously_distributed' => $previouslyDistributed,

                'delivery_receipt_remaining' => $deliveryReceiptRemaining,

                'pooled_available' => $pooledAvailable,

                'available_for_projects' => $availableForProjects,
            ];
        }

        return $balances;
    }

    /**
     * Validate project quantities using only one Delivery Receipt.
     *
     * @param  array<int|string, mixed>  $submittedItems
     */
    public function validateProjectQuantities(
        DeliveryReceipt $receipt,
        array $submittedItems
    ): void {
        $balances = $this->balances(
            $receipt
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

            $itemId = (int) $itemId;

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
                    'This PPE item does not belong to the selected Delivery Receipt.';

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

                $errors[
                    "items.{$itemId}"
                ] =
                    "{$itemName} has only "
                    .number_format($available)
                    .' available from the selected Delivery Receipt.';
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
     * Sum project quantities linked specifically to this receipt.
     *
     * @param  array<int, int>  $itemIds
     * @return Collection<int|string, int>
     */
    private function distributedByItem(
        DeliveryReceipt $receipt,
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
                    $receipt
                ): void {
                    $query
                        ->where(
                            'delivery_receipt_id',
                            $receipt->id
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

    private function ensureProvinceAccess(
        DeliveryReceipt $receipt
    ): void {
        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        abort_unless(
            (int) $receipt->province_id
                === (int) $provinceId,
            403,
            'You cannot use another province\'s Delivery Receipt.'
        );
    }

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
