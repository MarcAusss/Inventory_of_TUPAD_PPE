<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\ProvincialInventory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProvincialInventorySummaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $provinceId = $request->integer('province_id') ?: null;

        /*
        |--------------------------------------------------------------------------
        | Province dropdown options
        |--------------------------------------------------------------------------
        */

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Provinces displayed in the summary
        |--------------------------------------------------------------------------
        */

        $visibleProvinces = Province::query()
            ->when(
                $provinceId,
                fn ($query) => $query->whereKey($provinceId)
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . $search . '%'
                )
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Inventory rows for visible provinces
        |--------------------------------------------------------------------------
        */

        $inventoryRows = ProvincialInventory::query()
            ->whereIn(
                'province_id',
                $visibleProvinces->pluck('id')
            )
            ->get()
            ->groupBy('province_id');

        /*
        |--------------------------------------------------------------------------
        | Provincial PPE summary
        |--------------------------------------------------------------------------
        |
        | Item ID mapping:
        |
        | 1 = Long Sleeves Medium
        | 2 = Long Sleeves Large
        | 3 = Bucket Hat
        | 4 = Rubber Boots US9
        | 5 = Rubber Boots US10
        | 6 = Hand Gloves
        | 7 = Mask
        |
        */

        $summaries = $visibleProvinces
            ->map(function (Province $province) use ($inventoryRows): array {
                $items = $inventoryRows
                    ->get($province->id, collect())
                    ->keyBy('item_id');

                $longMedium = (int) (
                    $items->get(1)?->quantity ?? 0
                );

                $longLarge = (int) (
                    $items->get(2)?->quantity ?? 0
                );

                $bucketHat = (int) (
                    $items->get(3)?->quantity ?? 0
                );

                $bootsUs9 = (int) (
                    $items->get(4)?->quantity ?? 0
                );

                $bootsUs10 = (int) (
                    $items->get(5)?->quantity ?? 0
                );

                $gloves = (int) (
                    $items->get(6)?->quantity ?? 0
                );

                $mask = (int) (
                    $items->get(7)?->quantity ?? 0
                );

                $longTotal =
                    $longMedium
                    + $longLarge;

                $bootsTotal =
                    $bootsUs9
                    + $bootsUs10;

                $total =
                    $longTotal
                    + $bucketHat
                    + $bootsTotal
                    + $gloves
                    + $mask;

                return [
                    'province' => $province,

                    'long_medium' => $longMedium,
                    'long_large' => $longLarge,
                    'long_total' => $longTotal,

                    'bucket_hat' => $bucketHat,

                    'boots_9' => $bootsUs9,
                    'boots_10' => $bootsUs10,
                    'boots_total' => $bootsTotal,

                    'gloves' => $gloves,
                    'mask' => $mask,

                    'total' => $total,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Consolidated totals
        |--------------------------------------------------------------------------
        */

        $totals = [
            'long_medium' => (int) $summaries->sum(
                'long_medium'
            ),

            'long_large' => (int) $summaries->sum(
                'long_large'
            ),

            'long_total' => (int) $summaries->sum(
                'long_total'
            ),

            'bucket_hat' => (int) $summaries->sum(
                'bucket_hat'
            ),

            'boots_9' => (int) $summaries->sum(
                'boots_9'
            ),

            'boots_10' => (int) $summaries->sum(
                'boots_10'
            ),

            'boots_total' => (int) $summaries->sum(
                'boots_total'
            ),

            'gloves' => (int) $summaries->sum(
                'gloves'
            ),

            'mask' => (int) $summaries->sum(
                'mask'
            ),

            'total' => (int) $summaries->sum(
                'total'
            ),
        ];

        $totalAvailable = $totals['total'];

        $totalProvinces = $summaries->count();

        return view(
            'accounting.provincial-inventory.index',
            compact(
                'search',
                'provinceId',
                'provinces',
                'summaries',
                'totals',
                'totalAvailable',
                'totalProvinces'
            )
        );
    }
}