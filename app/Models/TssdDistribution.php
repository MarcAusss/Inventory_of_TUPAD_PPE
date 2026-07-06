<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TSSDDistribution extends Model
{
    protected $table = 'tssd_distributions';

    protected $fillable = [
        'purchase_order_id',
        'province_id',
        'item_id',
        'quantity',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
}