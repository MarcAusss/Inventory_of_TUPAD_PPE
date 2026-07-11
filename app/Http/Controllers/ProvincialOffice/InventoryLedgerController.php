<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $currentYear = now()->year;

        $year = (int) $request->query(
            'year',
            $currentYear
        );

        if ($year < 2000 || $year > 2100) {
            $year = $currentYear;
        }

        $search = trim(
            (string) $request->query('search')
        );

        $movements = InventoryMovement::query()
            ->with([
                'item',
                'deliveryReceipt',
                'supplyDesignation',
                'creator',
            ])
            ->forProvince($provinceId)
            ->forYear($year)
            ->when(
                $search,
                function ($query) use ($search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'reference_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'item',
                                    function ($itemQuery) use ($search): void {
                                        $itemQuery
                                            ->where(
                                                'item_name',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'label',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->latest('movement_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = $this->buildYearSummary(
            $provinceId,
            $year
        );

        $availableYears = InventoryMovement::query()
            ->forProvince($provinceId)
            ->selectRaw(
                'YEAR(movement_date) as movement_year'
            )
            ->distinct()
            ->orderByDesc('movement_year')
            ->pluck('movement_year')
            ->map(fn ($value): int => (int) $value);

        if (! $availableYears->contains($currentYear)) {
            $availableYears->prepend($currentYear);
        }

        return view(
            'provincial.inventory-ledger.index',
            compact(
                'movements',
                'summary',
                'year',
                'currentYear',
                'availableYears',
                'search'
            )
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildYearSummary(
        int $provinceId,
        int $year
    ): array {
        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return $items
            ->map(function (Item $item) use (
                $provinceId,
                $year
            ): array {
                $beginningInventory = $this->balanceBeforeYear(
                    $provinceId,
                    $item->id,
                    $year
                );

                $received = InventoryMovement::query()
                    ->forProvince($provinceId)
                    ->forYear($year)
                    ->where('item_id', $item->id)
                    ->stockIn()
                    ->sum('quantity');

                $issued = InventoryMovement::query()
                    ->forProvince($provinceId)
                    ->forYear($year)
                    ->where('item_id', $item->id)
                    ->stockOut()
                    ->sum('quantity');

                $endingInventory =
                    $beginningInventory
                    + (int) $received
                    - (int) $issued;

                return [
                    'item' => $item,
                    'beginning_inventory' => $beginningInventory,
                    'received_inventory' => (int) $received,
                    'issued_inventory' => (int) $issued,
                    'actual_inventory' => $endingInventory,
                    'ending_inventory' => $endingInventory,
                ];
            })
            ->all();
    }

    private function balanceBeforeYear(
        int $provinceId,
        int $itemId,
        int $year
    ): int {
        $stockIn = InventoryMovement::query()
            ->forProvince($provinceId)
            ->where('item_id', $itemId)
            ->whereDate(
                'movement_date',
                '<',
                "{$year}-01-01"
            )
            ->stockIn()
            ->sum('quantity');

        $stockOut = InventoryMovement::query()
            ->forProvince($provinceId)
            ->where('item_id', $itemId)
            ->whereDate(
                'movement_date',
                '<',
                "{$year}-01-01"
            )
            ->stockOut()
            ->sum('quantity');

        return (int) $stockIn - (int) $stockOut;
    }
}
