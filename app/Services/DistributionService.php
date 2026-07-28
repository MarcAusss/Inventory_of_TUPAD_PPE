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
     * Create a normalized TSSD distribution batch using the active PPE item
     * records submitted by the dynamic provincial-allocation form.
     *
     * @param  array<string, mixed>  $data
     * @throws Throwable
     */
    public function createBatch(array $data): TssdDistributionBatch
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->isTssd(),
            403,
            'Only the TSSD Unit may create distributions.'
        );

        return DB::transaction(function () use ($data, $user): TssdDistributionBatch {
            $purchaseOrder = PurchaseOrder::query()
                ->with('items.item')
                ->lockForUpdate()
                ->findOrFail((int) $data['purchase_order_id']);

            if (!in_array($purchaseOrder->status, ['Pending Distribution', 'Distributed'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'This Purchase Order is no longer available for distribution.',
                ]);
            }

            $distributions = $this->normalizeDistributions($data['distributions'] ?? []);

            if ($distributions === []) {
                throw ValidationException::withMessages([
                    'distributions' => 'Assign PPE to at least one province.',
                ]);
            }

            $items = $this->resolveSubmittedItems($distributions);

            $purchaseOrderItems = PurchaseOrderItem::query()
                ->with('item')
                ->where('purchase_order_id', $purchaseOrder->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('item_id');

            $requestedByItem = $this->calculateRequestedTotals($distributions);
            $remainingByItem = $this->calculateRemainingByItem($purchaseOrder, $purchaseOrderItems);

            $this->validateRequestedTotals($requestedByItem, $remainingByItem, $items);

            $batch = TssdDistributionBatch::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'created_by' => $user->id,
                'distribution_date' => now()->toDateString(),
                'status' => 'Submitted',
                'remarks' => $data['remarks'] ?? null,
                'call_off_letter_nefa_title' => $data['nefa_title'],
                'call_off_letter_total_amount' => $data['print_total_amount'],
                'call_off_letter_margin_top' => $data['print_margin_top'],
                'call_off_letter_margin_right' => $data['print_margin_right'],
                'call_off_letter_margin_bottom' => $data['print_margin_bottom'],
                'call_off_letter_margin_left' => $data['print_margin_left'],
                'call_off_letter_submitted_at' => now(),
            ]);

            foreach ($distributions as $distributionIndex => $distribution) {
                $province = Province::query()->findOrFail($distribution['province_id']);

                $provinceDistribution = $batch->provinceDistributions()->create([
                    'province_id' => $province->id,
                    'scheduled_delivery_date' => $distribution['scheduled_delivery_date'],
                    'place_of_delivery' => $province->deliveryLocation(),
                    'status' => 'Pending',
                    'remarks' => $distribution['remarks'] ?? null,
                ]);

                $positiveItems = collect($distribution['items'])
                    ->filter(fn (int $quantity): bool => $quantity > 0);

                if ($positiveItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        "distributions.{$distributionIndex}.items" => "Enter at least one PPE quantity for {$province->name}.",
                    ]);
                }

                foreach ($positiveItems as $itemId => $quantity) {
                    $provinceDistribution->items()->create([
                        'item_id' => (int) $itemId,
                        'quantity' => (int) $quantity,
                    ]);
                }
            }

            $purchaseOrder->update(['status' => 'Distributed']);

            return $batch->fresh([
                'purchaseOrder.supplier',
                'creator',
                'provinceDistributions.province',
                'provinceDistributions.items.item',
            ]);
        }, attempts: 3);
    }

    /** @return array<int, array<string, mixed>> */
    private function normalizeDistributions(mixed $distributions): array
    {
        if (!is_array($distributions)) {
            throw ValidationException::withMessages([
                'distributions' => 'The submitted distribution data is invalid.',
            ]);
        }

        $normalized = [];

        foreach ($distributions as $index => $distribution) {
            if (!is_array($distribution)) {
                throw ValidationException::withMessages([
                    "distributions.{$index}" => 'This province distribution entry is invalid.',
                ]);
            }

            $provinceId = filter_var($distribution['province_id'] ?? null, FILTER_VALIDATE_INT);

            if ($provinceId === false || (int) $provinceId <= 0) {
                throw ValidationException::withMessages([
                    "distributions.{$index}.province_id" => 'Select a valid province.',
                ]);
            }

            $deliveryDate = trim((string) ($distribution['scheduled_delivery_date'] ?? ''));

            if ($deliveryDate === '') {
                throw ValidationException::withMessages([
                    "distributions.{$index}.scheduled_delivery_date" => 'Every province must have its own delivery date.',
                ]);
            }

            $submittedItems = $distribution['items'] ?? null;

            if (!is_array($submittedItems)) {
                throw ValidationException::withMessages([
                    "distributions.{$index}.items" => 'The PPE quantities are invalid.',
                ]);
            }

            $items = [];

            foreach ($submittedItems as $itemId => $quantity) {
                $validatedItemId = filter_var($itemId, FILTER_VALIDATE_INT);
                $validatedQuantity = filter_var($quantity === '' ? 0 : $quantity, FILTER_VALIDATE_INT);

                if ($validatedItemId === false || (int) $validatedItemId <= 0) {
                    throw ValidationException::withMessages([
                        "distributions.{$index}.items" => 'One PPE item identifier is invalid.',
                    ]);
                }

                if ($validatedQuantity === false || (int) $validatedQuantity < 0) {
                    throw ValidationException::withMessages([
                        "distributions.{$index}.items.{$itemId}" => 'The PPE quantity must be a non-negative whole number.',
                    ]);
                }

                $items[(int) $validatedItemId] = (int) $validatedQuantity;
            }

            $normalized[] = [
                'province_id' => (int) $provinceId,
                'scheduled_delivery_date' => $deliveryDate,
                'remarks' => isset($distribution['remarks'])
                    ? trim((string) $distribution['remarks'])
                    : null,
                'items' => $items,
            ];
        }

        if (collect($normalized)->pluck('province_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'distributions' => 'A province cannot appear more than once in the same distribution batch.',
            ]);
        }

        return $normalized;
    }

    /** @param array<int, array<string, mixed>> $distributions
     *  @return Collection<int, Item>
     */
    private function resolveSubmittedItems(array $distributions): Collection
    {
        $itemIds = collect($distributions)
            ->flatMap(fn (array $distribution) => array_keys($distribution['items']))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $items = Item::query()
            ->whereIn('id', $itemIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($items->count() !== $itemIds->count()) {
            throw ValidationException::withMessages([
                'distributions' => 'One or more PPE items are disabled, removed, or no longer available. Refresh the page and try again.',
            ]);
        }

        return $items;
    }

    /** @param array<int, array<string, mixed>> $distributions
     *  @return array<int, int>
     */
    private function calculateRequestedTotals(array $distributions): array
    {
        $totals = [];

        foreach ($distributions as $distribution) {
            foreach ($distribution['items'] as $itemId => $quantity) {
                $totals[(int) $itemId] = ($totals[(int) $itemId] ?? 0) + (int) $quantity;
            }
        }

        return $totals;
    }

    /** @param Collection<int, PurchaseOrderItem> $purchaseOrderItems
     *  @return array<int, int>
     */
    private function calculateRemainingByItem(PurchaseOrder $purchaseOrder, Collection $purchaseOrderItems): array
    {
        $remaining = [];

        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $remaining[(int) $purchaseOrderItem->item_id] = (int) $purchaseOrderItem->quantity;
        }

        $legacyTotals = TSSDDistribution::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        $normalizedTotals = ProvinceDistributionItem::query()
            ->whereHas('provinceDistribution.distributionBatch', function ($query) use ($purchaseOrder): void {
                $query->where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', '!=', 'Cancelled');
            })
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        foreach (array_keys($remaining) as $itemId) {
            $used = (int) ($legacyTotals[$itemId] ?? 0)
                + (int) ($normalizedTotals[$itemId] ?? 0);

            $remaining[$itemId] = max(0, $remaining[$itemId] - $used);
        }

        return $remaining;
    }

    /** @param array<int, int> $requestedByItem
     *  @param array<int, int> $remainingByItem
     *  @param Collection<int, Item> $items
     */
    private function validateRequestedTotals(array $requestedByItem, array $remainingByItem, Collection $items): void
    {
        $errors = [];

        foreach ($requestedByItem as $itemId => $requested) {
            $remaining = (int) ($remainingByItem[$itemId] ?? 0);

            if ($requested <= $remaining) {
                continue;
            }

            $item = $items->get($itemId);
            $displayName = $item
                ? $item->item_name . ($item->label ? " ({$item->label})" : '')
                : "PPE Item #{$itemId}";

            $errors["items.{$itemId}"] = $displayName . ' has '
                . number_format($remaining)
                . ' remaining in this Purchase Order, but '
                . number_format($requested)
                . ' was allocated across all provinces.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
