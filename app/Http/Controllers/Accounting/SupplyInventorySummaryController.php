<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplyInventorySummaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        /*
        |--------------------------------------------------------------------------
        | Eligible Purchase Orders
        |--------------------------------------------------------------------------
        |
        | Include only Purchase Orders that do not have any non-cancelled
        | provincial distribution.
        |
        */

        $eligiblePurchaseOrder = function (Builder $query): void {
            $query->whereDoesntHave(
                'distributionBatches.provinceDistributions',
                function (Builder $provinceDistributionQuery): void {
                    $provinceDistributionQuery->where(
                        'status',
                        '!=',
                        'Cancelled'
                    );
                }
            );
        };

        /*
        |--------------------------------------------------------------------------
        | Inventory Summary
        |--------------------------------------------------------------------------
        */

        $inventories = Item::query()
            ->with([
                'purchaseOrderItems' => function (
                    $purchaseOrderItemQuery
                ) use ($eligiblePurchaseOrder): void {
                    /*
                     * Do not type-hint this parameter as Builder.
                     * Laravel passes the HasMany relationship here.
                     */

                    $purchaseOrderItemQuery
                        ->whereHas(
                            'purchaseOrder',
                            $eligiblePurchaseOrder
                        )
                        ->with([
                            'purchaseOrder.supplier',
                        ])
                        ->orderByDesc('purchase_order_id')
                        ->orderByDesc('id');
                },
            ])
            ->withSum(
                [
                    'purchaseOrderItems as available_quantity' => function (
                        Builder $purchaseOrderItemQuery
                    ) use ($eligiblePurchaseOrder): void {
                        $purchaseOrderItemQuery->whereHas(
                            'purchaseOrder',
                            $eligiblePurchaseOrder
                        );
                    },
                ],
                'quantity'
            )
            ->when(
                $search !== '',
                function (Builder $itemQuery) use ($search): void {
                    $itemQuery->where(
                        function (Builder $searchQuery) use ($search): void {
                            $searchQuery
                                ->where(
                                    'item_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'label',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'unit_of_measurement',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('item_name')
            ->orderBy('label')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Total Available Quantity
        |--------------------------------------------------------------------------
        */

        $totalAvailable = (int) PurchaseOrderItem::query()
            ->whereHas(
                'purchaseOrder',
                $eligiblePurchaseOrder
            )
            ->sum('quantity');

        return view(
            'accounting.supply-inventory.index',
            [
                'inventories' => $inventories,
                'search' => $search,
                'totalAvailable' => $totalAvailable,
            ]
        );
    }
}