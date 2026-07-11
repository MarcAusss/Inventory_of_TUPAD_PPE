<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Models\ProvincialInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display the current inventory of the logged-in province.
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

        $inventories = ProvincialInventory::query()
            ->with([
                'province',
                'item',
            ])
            ->forProvince($provinceId)
            ->search($search)
            ->orderByDesc('quantity')
            ->orderBy('item_id')
            ->paginate(10)
            ->withQueryString();

        $summary = $this->buildInventorySummary(
            $provinceId
        );

        $totalQuantity = ProvincialInventory::query()
            ->forProvince($provinceId)
            ->sum('quantity');

        $availableItemTypes = ProvincialInventory::query()
            ->forProvince($provinceId)
            ->where('quantity', '>', 0)
            ->count();

        $recentReceipts = DeliveryReceipt::query()
            ->with([
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                'items.item',
            ])
            ->where('province_id', $provinceId)
            ->latest('delivery_date')
            ->limit(5)
            ->get();

        return view(
            'provincial.inventory.current',
            compact(
                'inventories',
                'summary',
                'totalQuantity',
                'availableItemTypes',
                'recentReceipts',
                'search'
            )
        );
    }

    /**
     * Build the fixed seven-PPE summary required by the system.
     *
     * @return array<string, int>
     */
    private function buildInventorySummary(
        int $provinceId
    ): array {
        $summary = [
            'long_sleeve_medium' => 0,
            'long_sleeve_large' => 0,
            'bucket_hat' => 0,
            'rubber_boots_us9' => 0,
            'rubber_boots_us10' => 0,
            'hand_gloves' => 0,
            'mask' => 0,
        ];

        $inventories = ProvincialInventory::query()
            ->with('item')
            ->forProvince($provinceId)
            ->get();

        foreach ($inventories as $inventory) {
            $key = $this->itemKey(
                $inventory->item?->item_name,
                $inventory->item?->label
            );

            if ($key !== null) {
                $summary[$key] +=
                    (int) $inventory->quantity;
            }
        }

        return $summary;
    }

    private function itemKey(
        ?string $itemName,
        ?string $label
    ): ?string {
        return match (true) {
            $itemName === 'Long Sleeve'
                && $label === 'Medium' => 'long_sleeve_medium',

            $itemName === 'Long Sleeve'
                && $label === 'Large' => 'long_sleeve_large',

            $itemName === 'Bucket Hat' => 'bucket_hat',

            $itemName === 'Rubber Boots'
                && $label === 'US9' => 'rubber_boots_us9',

            $itemName === 'Rubber Boots'
                && $label === 'US10' => 'rubber_boots_us10',

            $itemName === 'Hand Gloves' => 'hand_gloves',

            $itemName === 'Mask' => 'mask',

            default => null,
        };
    }
}
