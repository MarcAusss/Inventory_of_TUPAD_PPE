<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplyInventorySummaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $inventories = Inventory::query()
            ->with('item')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('item', function ($itemQuery) use ($search): void {
                    $itemQuery
                        ->where('item_name', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%")
                        ->orWhere('unit_of_measurement', 'like', "%{$search}%");
                });
            })
            ->orderBy('item_id')
            ->paginate(20)
            ->withQueryString();

        return view('accounting.supply-inventory.index', [
            'inventories' => $inventories,
            'search' => $search,
            'totalAvailable' => (int) Inventory::query()->sum('quantity'),
        ]);
    }
}
