<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\PurchaseOrder;
use App\Models\TSSDDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TssdDistributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->latest()
            ->paginate(10);

        return view('tssd.distribution.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with([
            'supplier',
            'items.item',
        ])->latest()->get();

        $provinces = Province::all();

        $purchaseOrderId = $request->purchase_order_id;

        $provinceDistributions = collect();

        if ($purchaseOrderId) {
            $provinceDistributions = TSSDDistribution::with(['province', 'item'])
                ->where('purchase_order_id', $purchaseOrderId)
                ->get()
                ->groupBy('province_id');
        }

        return view('tssd.distribution.create', compact(
            'purchaseOrders',
            'provinces',
            'provinceDistributions',
            'purchaseOrderId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'purchase_order_id' => 'required',
            'delivery_date' => 'required|date',
            'distributions' => 'required',
        ]);

        if ($validator->fails()) {
            dd($validator->errors(), $request->all());
        }

        $distributions = json_decode($request->distributions, true) ?? [];

        if (empty($distributions)) {
            return response()->json([
                'success' => false,
                'message' => 'No distribution data received'
            ], 422);
        }

        // -----------------------------
        // STEP 1: GET PURCHASE ORDER STOCK
        // -----------------------------
        $purchaseOrder = PurchaseOrder::with('items.item')
            ->findOrFail($request->purchase_order_id);

        $stock = [
            'lsm' => 0,
            'lsl' => 0,
            'bucket' => 0,
            'us9' => 0,
            'us10' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];

        foreach ($purchaseOrder->items as $item) {

            $name = $item->item->item_name ?? '';
            $label = $item->item->label ?? '';

            if ($name === "Long Sleeve" && $label === "Medium")
                $stock['lsm'] = $item->quantity;
            if ($name === "Long Sleeve" && $label === "Large")
                $stock['lsl'] = $item->quantity;

            if ($name === "Bucket Hat")
                $stock['bucket'] = $item->quantity;
            if ($name === "Rubber Boots" && $label === "US9")
                $stock['us9'] = $item->quantity;
            if ($name === "Rubber Boots" && $label === "US10")
                $stock['us10'] = $item->quantity;

            if ($name === "Hand Gloves")
                $stock['gloves'] = $item->quantity;
            if ($name === "Mask")
                $stock['mask'] = $item->quantity;
        }

        // -----------------------------
        // STEP 2: CALCULATE TOTAL REQUESTED
        // -----------------------------
        $used = [
            'lsm' => 0,
            'lsl' => 0,
            'bucket' => 0,
            'us9' => 0,
            'us10' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];

        foreach ($distributions as $row) {

            foreach ($used as $key => $value) {
                $used[$key] += $row[$key] ?? 0;
            }
        }

        // -----------------------------
        // STEP 3: VALIDATE STOCK
        // -----------------------------
        foreach ($used as $key => $value) {

            if ($value > $stock[$key]) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$key}"
                ], 422);
            }
        }

        // -----------------------------
        // STEP 4: SAVE
        // -----------------------------
        // -----------------------------
// STEP 4: SAVE
// -----------------------------
        DB::transaction(function () use ($purchaseOrder, $distributions) {

            $map = [
                [
                    'field' => 'long_sleeve_medium',
                    'item' => 'Long Sleeve',
                    'label' => 'Medium',
                ],
                [
                    'field' => 'long_sleeve_large',
                    'item' => 'Long Sleeve',
                    'label' => 'Large',
                ],
                [
                    'field' => 'bucket_hat',
                    'item' => 'Bucket Hat',
                    'label' => null,
                ],
                [
                    'field' => 'rubber_boots_us9',
                    'item' => 'Rubber Boots',
                    'label' => 'US9',
                ],
                [
                    'field' => 'rubber_boots_us10',
                    'item' => 'Rubber Boots',
                    'label' => 'US10',
                ],
                [
                    'field' => 'hand_gloves',
                    'item' => 'Hand Gloves',
                    'label' => null,
                ],
                [
                    'field' => 'mask',
                    'item' => 'Mask',
                    'label' => null,
                ],
            ];

            foreach ($distributions as $row) {
                foreach ($map as $ppe) {

                    $qty = $row[$ppe['field']] ?? 0;

                    if ($qty <= 0)
                        continue;

                    $itemId = $this->getItemId(
                        $purchaseOrder,
                        $ppe['item'],
                        $ppe['label']
                    );

                    if (!$itemId)
                        continue;

                    TSSDDistribution::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'province_id' => $row['province_id'],
                        'item_id' => $itemId,
                        'quantity' => $qty,
                    ]);
                }
            }
        });

        // ✅ ADD THIS RESPONSE (VERY IMPORTANT)
        return response()->json([
            'success' => true,
            'message' => 'Distribution saved successfully'
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'items.item'
        ])->findOrFail($id);

        $provinces = Province::all();

        // Load distributions with item and province
        $distributionRecords = TSSDDistribution::with(['province', 'item'])
            ->where('purchase_order_id', $id)
            ->get();

        // Group distributions by province
        $distributions = $distributionRecords->groupBy('province_id');

        // Purchased totals
        $purchased = [
            'lsm' => 0,
            'lsl' => 0,
            'us9' => 0,
            'us10' => 0,
            'bucket' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];

        foreach ($purchaseOrder->items as $poItem) {

            $name = $poItem->item->item_name ?? '';
            $label = $poItem->item->label ?? '';

            if ($name == 'Long Sleeve' && $label == 'Medium') {
                $purchased['lsm'] += $poItem->quantity;
            }

            if ($name == 'Long Sleeve' && $label == 'Large') {
                $purchased['lsl'] += $poItem->quantity;
            }

            if ($name == 'Rubber Boots' && $label == 'US9') {
                $purchased['us9'] += $poItem->quantity;
            }

            if ($name == 'Rubber Boots' && $label == 'US10') {
                $purchased['us10'] += $poItem->quantity;
            }

            if ($name == 'Bucket Hat') {
                $purchased['bucket'] += $poItem->quantity;
            }

            if ($name == 'Hand Gloves') {
                $purchased['gloves'] += $poItem->quantity;
            }

            if ($name == 'Mask') {
                $purchased['mask'] += $poItem->quantity;
            }
        }

        // Distributed totals
        $distributed = [
            'lsm' => 0,
            'lsl' => 0,
            'us9' => 0,
            'us10' => 0,
            'bucket' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];

        foreach ($distributionRecords as $record) {

            $name = $record->item->item_name ?? '';
            $label = $record->item->label ?? '';

            if ($name == 'Long Sleeve' && $label == 'Medium') {
                $distributed['lsm'] += $record->quantity;
            }

            if ($name == 'Long Sleeve' && $label == 'Large') {
                $distributed['lsl'] += $record->quantity;
            }

            if ($name == 'Rubber Boots' && $label == 'US9') {
                $distributed['us9'] += $record->quantity;
            }

            if ($name == 'Rubber Boots' && $label == 'US10') {
                $distributed['us10'] += $record->quantity;
            }

            if ($name == 'Bucket Hat') {
                $distributed['bucket'] += $record->quantity;
            }

            if ($name == 'Hand Gloves') {
                $distributed['gloves'] += $record->quantity;
            }

            if ($name == 'Mask') {
                $distributed['mask'] += $record->quantity;
            }
        }

        // Remaining stock
        $remaining = [];

        foreach ($purchased as $key => $qty) {
            $remaining[$key] = $qty - ($distributed[$key] ?? 0);
        }

        return view('tssd.distribution.show', compact(
            'purchaseOrder',
            'provinces',
            'distributions',
            'purchased',
            'distributed',
            'remaining'
        ));
    }

    // ---------------------------
// KEY MAPPER (VERY IMPORTANT)
// ---------------------------
    private function mapKey($name, $label)
    {
        return match (true) {

            $name === 'Long Sleeve' && $label === 'Medium' => 'lsm',
            $name === 'Long Sleeve' && $label === 'Large' => 'lsl',

            $name === 'Rubber Boots' && $label === 'US9' => 'us9',
            $name === 'Rubber Boots' && $label === 'US10' => 'us10',

            $name === 'Bucket Hat' => 'bucket',
            $name === 'Hand Gloves' => 'gloves',
            $name === 'Mask' => 'mask',

            default => 'unknown'
        };
    }

    private function getItemId($purchaseOrder, $itemName, $label = null)
    {
        $item = $purchaseOrder->items->first(function ($i) use ($itemName, $label) {

            if (!$i->item) {
                return false;
            }

            if ($i->item->item_name != $itemName) {
                return false;
            }

            if ($label === null) {
                return true;
            }

            return $i->item->label == $label;
        });

        return $item?->item_id;
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

    public function getRemaining($poId)
    {
        $po = PurchaseOrder::with(['items.item'])->findOrFail($poId);

        // 1. PURCHASED STOCK
        $purchased = [];

        foreach ($po->items as $item) {

            $key = $this->mapKey(
                $item->item->item_name,
                $item->item->label ?? null
            );

            $purchased[$key] = ($purchased[$key] ?? 0) + $item->quantity;
        }

        // 2. DISTRIBUTED STOCK (FIXED QUERY)
        $distributedRows = DB::table('tssd_distributions')
            ->join('items', 'items.id', '=', 'tssd_distributions.item_id')
            ->where('tssd_distributions.purchase_order_id', $poId)
            ->select(
                'items.item_name',
                'items.label',
                DB::raw('SUM(tssd_distributions.quantity) as qty')
            )
            ->groupBy('items.item_name', 'items.label')
            ->get();

        $used = [];

        foreach ($distributedRows as $row) {
            $key = $this->mapKey($row->item_name, $row->label);
            $used[$key] = (int) $row->qty;
        }
        // 3. REMAINING
        $remaining = [];

        foreach ($purchased as $key => $qty) {
            $remaining[$key] = $qty - ($used[$key] ?? 0);
        }

        return response()->json([
            'remaining' => $remaining
        ]);
    }
}
