<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryReceiptItem extends Model
{
    protected $fillable = [

        'delivery_receipt_id',

        'item_id',

        'quantity',

    ];

    public function deliveryReceipt()
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}