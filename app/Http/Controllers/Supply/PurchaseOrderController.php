<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($purchaseOrderQuery) use ($search): void {
                    $purchaseOrderQuery
                        ->where('po_number', 'like', "%{$search}%")
                        ->orWhere('nefa_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('purchase-orders.index', compact(
            'purchaseOrders',
            'search'
        ));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('supplier_name')
            ->get();

        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->orderBy('label')
            ->get();

        return view('purchase-orders.create', compact(
            'suppliers',
            'items'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'po_number' => ['required', 'string', 'max:255', 'unique:purchase_orders,po_number'],
            'po_date' => ['required', 'date'],
            'nefa_number' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $document = $request->hasFile('document')
                ? $request->file('document')->store('purchase-orders', 'local')
                : null;

            $totalAmount = collect($validated['items'])->sum(
                fn (array $item): float =>
                    (int) $item['quantity'] * (float) $item['unit_cost']
            );

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'created_by' => auth()->id(),
                'po_number' => $validated['po_number'],
                'po_date' => $validated['po_date'],
                'nefa_number' => $validated['nefa_number'],
                'total_amount' => $totalAmount,
                'document' => $document,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'Pending Distribution',
            ]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                $purchaseOrder->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                ]);

                /*
                |------------------------------------------------------------------
                | IMPORTANT FIX
                |------------------------------------------------------------------
                |
                | Inventory must be updated inside this foreach loop. Previously,
                | it was outside the loop, so only the last item was recorded.
                |
                */
                $inventory = Inventory::firstOrCreate(
                    ['item_id' => $item['item_id']],
                    ['quantity' => 0]
                );

                $inventory->increment('quantity', $quantity);
            }
        });

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with('success', 'Purchase Order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('supplier', 'items.item');

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('items');

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('supplier_name')
            ->get();

        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->orderBy('label')
            ->get();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'items'
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'po_number' => [
                'required',
                'string',
                'max:255',
                'unique:purchase_orders,po_number,' . $purchaseOrder->id,
            ],
            'po_date' => ['required', 'date'],
            'nefa_number' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $validated, $purchaseOrder): void {
            $document = $purchaseOrder->document;

            if ($request->hasFile('document')) {
                if (
                    $purchaseOrder->document !== null
                    && (
                        Storage::disk('local')->exists($purchaseOrder->document)
                        || Storage::disk('public')->exists($purchaseOrder->document)
                    )
                ) {
                    Storage::disk(Storage::disk('local')->exists($purchaseOrder->document) ? 'local' : 'public')->delete($purchaseOrder->document);
                }

                $document = $request->file('document')
                    ->store('purchase-orders', 'local');
            }

            /*
            |------------------------------------------------------------------
            | Save old ordered quantities before replacing PO items
            |------------------------------------------------------------------
            */
            $oldQuantities = $purchaseOrder->items()
                ->selectRaw('item_id, SUM(quantity) as total_quantity')
                ->groupBy('item_id')
                ->pluck('total_quantity', 'item_id')
                ->map(fn ($quantity): int => (int) $quantity);

            $newQuantities = collect($validated['items'])
                ->groupBy('item_id')
                ->map(fn ($rows): int => (int) collect($rows)->sum('quantity'));

            $purchaseOrder->items()->delete();

            $grandTotal = 0;

            foreach ($validated['items'] as $row) {
                $quantity = (int) $row['quantity'];
                $unitCost = (float) $row['unit_cost'];
                $lineTotal = $quantity * $unitCost;

                $purchaseOrder->items()->create([
                    'item_id' => $row['item_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                $grandTotal += $lineTotal;
            }

            /*
            |------------------------------------------------------------------
            | Adjust inventory only by the quantity difference
            |------------------------------------------------------------------
            */
            $itemIds = $oldQuantities->keys()
                ->merge($newQuantities->keys())
                ->unique();

            foreach ($itemIds as $itemId) {
                $oldQuantity = (int) $oldQuantities->get($itemId, 0);
                $newQuantity = (int) $newQuantities->get($itemId, 0);
                $difference = $newQuantity - $oldQuantity;

                $inventory = Inventory::firstOrCreate(
                    ['item_id' => $itemId],
                    ['quantity' => 0]
                );

                if ($difference > 0) {
                    $inventory->increment('quantity', $difference);
                } elseif ($difference < 0) {
                    $quantityToRemove = abs($difference);

                    if ($inventory->quantity < $quantityToRemove) {
                        abort(
                            422,
                            'The Purchase Order quantity cannot be reduced because some of its stock has already been distributed.'
                        );
                    }

                    $inventory->decrement('quantity', $quantityToRemove);
                }
            }

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'po_number' => $validated['po_number'],
                'po_date' => $validated['po_date'],
                'nefa_number' => $validated['nefa_number'],
                'remarks' => $validated['remarks'] ?? null,
                'document' => $document,
                'total_amount' => $grandTotal,
            ]);
        });

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with('success', 'Purchase Order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($purchaseOrder): void {
            $orderedQuantities = $purchaseOrder->items()
                ->selectRaw('item_id, SUM(quantity) as total_quantity')
                ->groupBy('item_id')
                ->pluck('total_quantity', 'item_id');

            foreach ($orderedQuantities as $itemId => $orderedQuantity) {
                $inventory = Inventory::query()
                    ->where('item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                $orderedQuantity = (int) $orderedQuantity;

                if ($inventory === null || $inventory->quantity < $orderedQuantity) {
                    abort(
                        422,
                        'This Purchase Order cannot be deleted because some of its stock has already been distributed.'
                    );
                }

                $inventory->decrement('quantity', $orderedQuantity);
            }

            if (
                $purchaseOrder->document !== null &&
                Storage::disk('local')->exists($purchaseOrder->document) || Storage::disk('public')->exists($purchaseOrder->document)
            ) {
                Storage::disk(Storage::disk('local')->exists($purchaseOrder->document) ? 'local' : 'public')->delete($purchaseOrder->document);
            }

            $purchaseOrder->delete();
        });

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }
}
