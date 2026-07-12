<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use App\Models\ProvincialInventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display the current inventory and Call-Off receiving records
     * belonging to the authenticated Provincial Office.
     */
    public function index(Request $request): View
    {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $search = trim(
            (string) $request->query('search')
        );

        /*
         * Current pooled provincial inventory.
         *
         * This remains the official stock available for project
         * designation.
         */
        $inventories = ProvincialInventory::query()
            ->with([
                'province',
                'item',
            ])
            ->forProvince($provinceId)
            ->search($search)
            ->orderByDesc('quantity')
            ->orderBy('item_id')
            ->paginate(
                10,
                ['*'],
                'inventory_page'
            )
            ->withQueryString();

        $summary = $this->buildInventorySummary(
            $provinceId
        );

        $totalQuantity = (int) ProvincialInventory::query()
            ->forProvince($provinceId)
            ->sum('quantity');

        $availableItemTypes = ProvincialInventory::query()
            ->forProvince($provinceId)
            ->where('quantity', '>', 0)
            ->count();

        /*
         * One summary record per Call-Off allocation for this province.
         *
         * Multiple DR quantities are added together under the same
         * Province Distribution / Call-Off allocation.
         */
        $callOffAllocations = ProvinceDistribution::query()
            ->with([
                'province',

                'distributionBatch.callOff',

                'distributionBatch.purchaseOrder.supplier',

                'items.item',

                'deliveryReceipts' => fn ($query) => $query
                    ->with([
                        'items.item',
                        'receivedByUser',
                    ])
                    ->orderBy('delivery_date')
                    ->orderBy('id'),
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereHas(
                'distributionBatch.callOff'
            )
            ->when(
                $search,
                function (
                    Builder $query
                ) use (
                    $search
                ): void {
                    $query->where(
                        function (
                            Builder $query
                        ) use (
                            $search
                        ): void {
                            $query
                                ->whereHas(
                                    'distributionBatch.callOff',
                                    fn (Builder $callOffQuery) => $callOffQuery->where(
                                        'call_off_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder',
                                    fn (Builder $purchaseOrderQuery) => $purchaseOrderQuery
                                        ->where(
                                            'po_number',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'nefa_number',
                                            'like',
                                            "%{$search}%"
                                        )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder.supplier',
                                    fn (Builder $supplierQuery) => $supplierQuery->where(
                                        'supplier_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'deliveryReceipts',
                                    fn (Builder $receiptQuery) => $receiptQuery->where(
                                        'dr_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );
                }
            )
            ->latest('scheduled_delivery_date')
            ->latest('id')
            ->paginate(
                8,
                ['*'],
                'call_off_page'
            )
            ->withQueryString();

        /*
         * Add calculated Allocation, Actual, and Remaining quantities
         * without changing the database records.
         */
        $callOffAllocations->through(
            function (
                ProvinceDistribution $allocation
            ): ProvinceDistribution {
                $allocationBreakdown =
                    $this->emptyPpeBreakdown();

                $actualBreakdown =
                    $this->emptyPpeBreakdown();

                foreach (
                    $allocation->items as $allocationItem
                ) {
                    $key = $this->itemKey(
                        $allocationItem
                            ->item
                            ?->item_name,

                        $allocationItem
                            ->item
                            ?->label
                    );

                    if ($key === null) {
                        continue;
                    }

                    $allocationBreakdown[$key] +=
                        (int) $allocationItem->quantity;
                }

                foreach (
                    $allocation->deliveryReceipts as $receipt
                ) {
                    foreach (
                        $receipt->items as $receiptItem
                    ) {
                        $key = $this->itemKey(
                            $receiptItem
                                ->item
                                ?->item_name,

                            $receiptItem
                                ->item
                                ?->label
                        );

                        if ($key === null) {
                            continue;
                        }

                        $actualBreakdown[$key] +=
                            (int) $receiptItem
                                ->received_quantity;
                    }
                }

                $remainingBreakdown =
                    $this->emptyPpeBreakdown();

                foreach (
                    array_keys(
                        $remainingBreakdown
                    ) as $key
                ) {
                    $remainingBreakdown[$key] = max(
                        0,
                        $allocationBreakdown[$key]
                            - $actualBreakdown[$key]
                    );
                }

                $allocation->setAttribute(
                    'allocation_breakdown',
                    $allocationBreakdown
                );

                $allocation->setAttribute(
                    'actual_breakdown',
                    $actualBreakdown
                );

                $allocation->setAttribute(
                    'remaining_breakdown',
                    $remainingBreakdown
                );

                $allocation->setAttribute(
                    'allocation_total',
                    array_sum(
                        $allocationBreakdown
                    )
                );

                $allocation->setAttribute(
                    'actual_total',
                    array_sum(
                        $actualBreakdown
                    )
                );

                $allocation->setAttribute(
                    'remaining_total',
                    array_sum(
                        $remainingBreakdown
                    )
                );

                return $allocation;
            }
        );

        /*
         * Keep this individual-DR section exactly as requested.
         *
         * If one Call-Off has two deliveries, both DR records appear
         * separately here.
         */
        $recentReceipts = DeliveryReceipt::query()
            ->with([
                'provinceDistribution.distributionBatch.callOff',

                'provinceDistribution.distributionBatch.purchaseOrder.supplier',

                'items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->latest('delivery_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return view(
            'provincial.inventory.current',
            compact(
                'inventories',
                'summary',
                'totalQuantity',
                'availableItemTypes',
                'callOffAllocations',
                'recentReceipts',
                'search'
            )
        );
    }

    /**
     * Build the fixed seven-PPE provincial stock summary.
     *
     * @return array<string, int>
     */
    private function buildInventorySummary(
        int $provinceId
    ): array {
        $summary =
            $this->emptyPpeBreakdown();

        $inventories = ProvincialInventory::query()
            ->with('item')
            ->forProvince($provinceId)
            ->get();

        foreach (
            $inventories as $inventory
        ) {
            $key = $this->itemKey(
                $inventory
                    ->item
                    ?->item_name,

                $inventory
                    ->item
                    ?->label
            );

            if ($key !== null) {
                $summary[$key] +=
                    (int) $inventory->quantity;
            }
        }

        return $summary;
    }

    /**
     * @return array<string, int>
     */
    private function emptyPpeBreakdown(): array
    {
        return [
            'long_sleeve_medium' => 0,
            'long_sleeve_large' => 0,
            'bucket_hat' => 0,
            'rubber_boots_us9' => 0,
            'rubber_boots_us10' => 0,
            'hand_gloves' => 0,
            'mask' => 0,
        ];
    }

    private function itemKey(
        ?string $itemName,
        ?string $label
    ): ?string {
        $normalizedName = strtolower(
            trim(
                (string) $itemName
            )
        );

        $normalizedLabel = strtolower(
            trim(
                (string) $label
            )
        );

        return match (true) {
            $normalizedName === 'long sleeve'
                && in_array(
                    $normalizedLabel,
                    [
                        'medium',
                        'm',
                    ],
                    true
                ) => 'long_sleeve_medium',

            $normalizedName === 'long sleeve'
                && in_array(
                    $normalizedLabel,
                    [
                        'large',
                        'l',
                    ],
                    true
                ) => 'long_sleeve_large',

            $normalizedName === 'bucket hat' => 'bucket_hat',

            $normalizedName === 'rubber boots'
                && in_array(
                    $normalizedLabel,
                    [
                        'us9',
                        'us 9',
                        '9',
                    ],
                    true
                ) => 'rubber_boots_us9',

            $normalizedName === 'rubber boots'
                && in_array(
                    $normalizedLabel,
                    [
                        'us10',
                        'us 10',
                        '10',
                    ],
                    true
                ) => 'rubber_boots_us10',

            in_array(
                $normalizedName,
                [
                    'hand gloves',
                    'gloves',
                ],
                true
            ) => 'hand_gloves',

            $normalizedName === 'mask' => 'mask',

            default => null,
        };
    }
}
