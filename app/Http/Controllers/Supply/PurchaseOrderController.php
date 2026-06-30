<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $purchaseOrders = PurchaseOrder::with('supplier')
            ->when($search, function ($query) use ($search) {
                $query->where('po_number', 'like', "%{$search}%")
                    ->orWhere('nefa_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('purchase-orders.index', compact(
            'purchaseOrders',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('supplier_name')
            ->get();

        $items = Item::where('is_active', true)
            ->orderBy('item_name')
            ->get();

        return view('purchase-orders.create', compact(
            'suppliers',
            'items'
        ));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | Purchase Order
            |--------------------------------------------------------------------------
            */

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'po_number' => [
                'required',
                'unique:purchase_orders,po_number',
            ],

            'po_date' => [
                'required',
                'date',
            ],

            'nefa_number' => [
                'required',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'document' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required',
                'exists:items,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

        ]);

        DB::transaction(function () use ($request, $validated) {

            /*
            |--------------------------------------------------------------------------
            | Upload File
            |--------------------------------------------------------------------------
            */

            $document = null;

            if ($request->hasFile('document')) {

                $document = $request
                    ->file('document')
                    ->store('purchase-orders', 'public');

            }

            /*
            |--------------------------------------------------------------------------
            | Calculate Total
            |--------------------------------------------------------------------------
            */

            $total = 0;

            foreach ($validated['items'] as $item) {

                $total +=
                    $item['quantity']
                    *
                    $item['unit_cost'];

            }

            /*
            |--------------------------------------------------------------------------
            | Create Purchase Order
            |--------------------------------------------------------------------------
            */

            $purchaseOrder = PurchaseOrder::create([

                'supplier_id' => $validated['supplier_id'],

                'created_by' => auth()->id(),

                'po_number' => $validated['po_number'],

                'po_date' => $validated['po_date'],

                'nefa_number' => $validated['nefa_number'],

                'total_amount' => $total,

                'document' => $document,

                'remarks' => $validated['remarks'] ?? null,

                'status' => 'Pending Distribution',

            ]);

            /*
            |--------------------------------------------------------------------------
            | Save Items
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $purchaseOrder->items()->create([

                    'item_id' => $item['item_id'],

                    'quantity' => $item['quantity'],

                    'unit_cost' => $item['unit_cost'],

                    'total_cost' => $item['quantity'] * $item['unit_cost'],

                    'size' => $item['size'] ?? null,

                ]);

            }
            $inventory = Inventory::firstOrCreate(
                [
                    'item_id' => $item['item_id']
                ],
                [
                    'quantity' => 0
                ]
            );

            $inventory->increment('quantity', $item['quantity']);

        });

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with('success', 'Purchase Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.item');

        return view('purchase-orders.show', compact(
            'purchaseOrder'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('supplier_name')
            ->get();

        $items = Item::where('is_active', true)
            ->orderBy('item_name')
            ->get();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'items'
        ));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'po_number' => [
                'required',
                'unique:purchase_orders,po_number,' . $purchaseOrder->id,
            ],

            'po_date' => [
                'required',
                'date',
            ],

            'nefa_number' => [
                'required',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'document' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required',
                'exists:items,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0.01',
            ],

        ]);

        DB::transaction(function () use ($request, $validated, $purchaseOrder) {
            dd($validated['items']);
            $document = $purchaseOrder->document;

            if ($request->hasFile('document')) {

                if (
                    $purchaseOrder->document &&
                    \Storage::disk('public')->exists($purchaseOrder->document)
                ) {

                    \Storage::disk('public')->delete($purchaseOrder->document);
                }

                $document = $request
                    ->file('document')
                    ->store('purchase-orders', 'public');
            }

            $purchaseOrder->update([

                'supplier_id' => $validated['supplier_id'],

                'po_number' => $validated['po_number'],

                'po_date' => $validated['po_date'],

                'nefa_number' => $validated['nefa_number'],

                'remarks' => $validated['remarks'] ?? null,

                'document' => $document,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Items
            |--------------------------------------------------------------------------
            */

            $purchaseOrder
                ->items()
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | Insert New Items
            |--------------------------------------------------------------------------
            */

            $grandTotal = 0;

            foreach ($validated['items'] as $row) {

                $lineTotal = $row['quantity'] * $row['unit_cost'];

                $purchaseOrder->items()->create([

                    'purchase_order_id' => $purchaseOrder->id,

                    'item_id' => $row['item_id'],

                    'quantity' => $row['quantity'],

                    'unit_cost' => $row['unit_cost'],

                    'total_cost' => $lineTotal,

                ]);

                $grandTotal += $lineTotal;
            }

            $purchaseOrder->update([
                'total_amount' => $grandTotal,
            ]);

        });

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with('success', 'Purchase Order updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('supply.purchase-orders.index')
            ->with(
                'success',
                'Purchase Order deleted successfully.'
            );
    }
}