<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Display all PPE items.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->query('search', '')
        );

        $status = trim(
            (string) $request->query('status', '')
        );

        $items = Item::query()
            ->withCount([
                'purchaseOrderItems',
                'tssdDistributions',
                'deliveryReceiptItems',
                'supplyDesignationItems',
                'provincialInventories',
            ])
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
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
            ->when(
                $status === 'active',
                fn (Builder $query): Builder =>
                    $query->where('is_active', true)
            )
            ->when(
                $status === 'inactive',
                fn (Builder $query): Builder =>
                    $query->where('is_active', false)
            )
            ->orderByDesc('is_active')
            ->orderBy('item_name')
            ->orderBy('label')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Item::query()->count(),

            'active' => Item::query()
                ->where('is_active', true)
                ->count(),

            'inactive' => Item::query()
                ->where('is_active', false)
                ->count(),
        ];

        return view(
            'supply.items.index',
            compact(
                'items',
                'search',
                'status',
                'summary'
            )
        );
    }

    /**
     * Show the form for creating a PPE item.
     */
    public function create(): View
    {
        return view('supply.items.create');
    }

    /**
     * Store a newly created PPE item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateItem(
            request: $request
        );

        $validated['item_name'] = $this->normalizeText(
            $validated['item_name']
        );

        $validated['label'] = $this->nullableNormalizedText(
            $validated['label'] ?? null
        );

        $validated['unit_of_measurement'] =
            $this->normalizeText(
                $validated['unit_of_measurement']
            );

        $validated['is_active'] =
            $request->boolean('is_active');

        $item = Item::query()->create($validated);

        return redirect()
            ->route('supply.items.show', $item)
            ->with(
                'success',
                'PPE item created successfully.'
            );
    }

    /**
     * Display one PPE item.
     */
    public function show(Item $item): View
    {
        $item->loadCount([
            'purchaseOrderItems',
            'tssdDistributions',
            'deliveryReceiptItems',
            'supplyDesignationItems',
            'provincialInventories',
        ]);

        $usage = [
            'purchase_orders' =>
                $item->purchase_order_items_count,

            'tssd_distributions' =>
                $item->tssd_distributions_count,

            'delivery_receipts' =>
                $item->delivery_receipt_items_count,

            'project_designations' =>
                $item->supply_designation_items_count,

            'provincial_inventories' =>
                $item->provincial_inventories_count,
        ];

        $usageTotal = array_sum($usage);

        return view(
            'supply.items.show',
            compact(
                'item',
                'usage',
                'usageTotal'
            )
        );
    }

    /**
     * Show the form for editing a PPE item.
     */
    public function edit(Item $item): View
    {
        return view(
            'supply.items.edit',
            compact('item')
        );
    }

    /**
     * Update a PPE item.
     */
    public function update(
        Request $request,
        Item $item
    ): RedirectResponse {
        $validated = $this->validateItem(
            request: $request,
            item: $item
        );

        $validated['item_name'] = $this->normalizeText(
            $validated['item_name']
        );

        $validated['label'] = $this->nullableNormalizedText(
            $validated['label'] ?? null
        );

        $validated['unit_of_measurement'] =
            $this->normalizeText(
                $validated['unit_of_measurement']
            );

        $validated['is_active'] =
            $request->boolean('is_active');

        $item->update($validated);

        return redirect()
            ->route('supply.items.show', $item)
            ->with(
                'success',
                'PPE item updated successfully.'
            );
    }

    /**
     * Toggle the availability of a PPE item.
     */
    public function toggleStatus(Item $item): RedirectResponse
    {
        $item->update([
            'is_active' => ! $item->is_active,
        ]);

        $message = $item->is_active
            ? 'PPE item marked as available.'
            : 'PPE item marked as unavailable. It will no longer appear in new Purchase Orders.';

        return back()->with('success', $message);
    }

    /**
     * Delete a PPE item only when it has no transaction history.
     */
    public function destroy(Item $item): RedirectResponse
    {
        if ($this->itemHasTransactionHistory($item)) {
            return back()->with(
                'error',
                'This PPE item cannot be deleted because it is already used in system transactions. Mark it as unavailable instead.'
            );
        }

        $item->delete();

        return redirect()
            ->route('supply.items.index')
            ->with(
                'success',
                'PPE item deleted successfully.'
            );
    }

    /**
     * Validate PPE item data.
     */
    private function validateItem(
        Request $request,
        ?Item $item = null
    ): array {
        return $request->validate([
            'item_name' => [
                'required',
                'string',
                'max:150',

                Rule::unique('items', 'item_name')
                    ->where(
                        fn ($query) => $query->where(
                            'label',
                            $this->nullableNormalizedText(
                                $request->input('label')
                            )
                        )
                    )
                    ->ignore($item?->id),
            ],

            'label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'unit_of_measurement' => [
                'required',
                'string',
                'max:100',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'item_name.unique' =>
                'An item with the same name and label already exists.',
        ]);
    }

    /**
     * Determine whether the item is already used.
     */
    private function itemHasTransactionHistory(Item $item): bool
    {
        return $item->purchaseOrderItems()->exists()
            || $item->tssdDistributions()->exists()
            || $item->deliveryReceiptItems()->exists()
            || $item->supplyDesignationItems()->exists()
            || $item->provincialInventories()->exists();
    }

    /**
     * Normalize required text.
     */
    private function normalizeText(string $value): string
    {
        return trim(
            preg_replace('/\s+/', ' ', $value)
            ?? $value
        );
    }

    /**
     * Normalize optional text.
     */
    private function nullableNormalizedText(
        mixed $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $value)
            ?? $value;
    }
}