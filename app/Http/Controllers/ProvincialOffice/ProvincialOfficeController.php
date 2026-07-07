<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TSSDDistribution;
use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptItem;
use Illuminate\Support\Facades\DB;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;


class ProvincialOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $provinceId = Auth::user()->province_id;

        $deliveries = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
        ])
            ->where('province_id', $provinceId)
            ->select('purchase_order_id')
            ->distinct()
            ->get()
            ->map(function ($delivery) use ($provinceId) {

                $delivery->items = TSSDDistribution::where(
                    'purchase_order_id',
                    $delivery->purchase_order_id
                )
                    ->where('province_id', $provinceId)
                    ->get();

                $delivery->receipt = DeliveryReceipt::where(
                    'purchase_order_id',
                    $delivery->purchase_order_id
                )
                    ->where('province_id', $provinceId)
                    ->first();

                return $delivery;
            });

        return view(
            'provincial.delivery.index',
            compact('deliveries')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($purchaseOrderId)
    {
        $provinceId = Auth::user()->province_id;

        $items = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
            'province',
            'item',
        ])
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('province_id', $provinceId)
            ->get();

        abort_if($items->isEmpty(), 404);

        $distribution = $items->first();

        $receipt = DeliveryReceipt::where('purchase_order_id', $purchaseOrderId)
            ->where('province_id', $provinceId)
            ->first();

        return view('provincial.delivery.show', [
            'distribution' => $distribution,
            'items' => $items,
            'receipt' => $receipt,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function receive($purchaseOrderId)
    {
        $provinceId = Auth::user()->province_id;

        $items = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
            'province',
            'item',
        ])
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('province_id', $provinceId)
            ->get();

        abort_if($items->isEmpty(), 404);

        $distribution = $items->first();

        $existingReceipt = DeliveryReceipt::where('purchase_order_id', $purchaseOrderId)
            ->where('province_id', $provinceId)
            ->first();

        if ($existingReceipt) {
            return redirect()
                ->route('provincial.deliveries.show', $purchaseOrderId)
                ->with('error', 'This delivery has already been received.');
        }

        return view('provincial.delivery.receive', [
            'distribution' => $distribution,
            'items' => $items,
        ]);
    }

    public function storeReceipt(Request $request, $purchaseOrderId)
    {
        $provinceId = Auth::user()->province_id;

        $distribution = TSSDDistribution::where(
            'purchase_order_id',
            $purchaseOrderId
        )
            ->where('province_id', $provinceId)
            ->firstOrFail();

        $request->validate([
            'dr_number' => 'required|unique:delivery_receipts,dr_number',
            'delivery_date' => 'required|date',
            'received_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $existingReceipt = DeliveryReceipt::where(
            'purchase_order_id',
            $purchaseOrderId
        )
            ->where('province_id', $provinceId)
            ->exists();

        if ($existingReceipt) {

            return back()->with(
                'error',
                'Delivery Receipt already exists.'
            );
        }

        DB::transaction(function () use ($request, $purchaseOrderId, $provinceId) {

            $receipt = DeliveryReceipt::create([

                'purchase_order_id' => $purchaseOrderId,

                'province_id' => $provinceId,

                'dr_number' => $request->dr_number,

                'delivery_date' => $request->delivery_date,

                'received_by' => $request->received_by,

                'remarks' => $request->remarks,

                'status' => 'Received',

            ]);

            foreach ($request->items as $itemId => $quantity) {

                DeliveryReceiptItem::create([

                    'delivery_receipt_id' => $receipt->id,

                    'item_id' => $itemId,

                    'quantity' => $quantity,

                ]);
            }
        });

        return redirect()
            ->route('provincial.deliveries.index')
            ->with(
                'success',
                'Delivery received successfully.'
            );
    }
    public function inventory()
    {
        $provinceId = Auth::user()->province_id;

        $inventories = ProvincialInventory::with([
            'item',
            'province',
        ])
            ->where('province_id', $provinceId)
            ->orderBy('item_id')
            ->get();

        return view(
            'provincial.inventory.index',
            compact('inventories')
        );
    }
    public function designate($inventoryId)
    {
        $provinceId = Auth::user()->province_id;

        $inventory = ProvincialInventory::with('item')
            ->where('province_id', $provinceId)
            ->findOrFail($inventoryId);

        return view(
            'provincial.inventory.designate',
            compact('inventory')
        );
    }
    public function storeDesignation(Request $request, $inventoryId)
    {
        $provinceId = Auth::user()->province_id;

        $inventory = ProvincialInventory::where(
            'province_id',
            $provinceId
        )->findOrFail($inventoryId);

        $request->validate([

            'project_name' => 'required|string|max:255',

            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $inventory->quantity,
            ],

            'remarks' => 'nullable|string',

        ]);

        DB::transaction(function () use ($request, $inventory, $provinceId) {

            SupplyDesignation::create([

                'province_inventory_id' => $inventory->id,

                'province_id' => $provinceId,

                'item_id' => $inventory->item_id,

                'project_name' => $request->project_name,

                'quantity' => $request->quantity,

                'remarks' => $request->remarks,

            ]);

            $inventory->decrement(
                'quantity',
                $request->quantity
            );

        });

        return redirect()
            ->route('provincial.inventory.index')
            ->with(
                'success',
                'PPE designated successfully.'
            );
    }
}
