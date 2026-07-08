<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryReceipt extends Model
{
    protected $fillable = [

        'purchase_order_id',
        'province_id',

        'dr_number',

        'delivery_date',

        'received_by',

        'remarks',

        'status',

    ];

    protected $casts = [

        'delivery_date' => 'date',

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryReceiptItem::class);
    }

    public function supplyDesignations()
    {
        return $this->hasMany(SupplyDesignation::class);
    }
    
}