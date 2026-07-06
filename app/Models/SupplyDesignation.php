<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyDesignation extends Model
{
    protected $fillable = [

        'delivery_receipt_id',

        'designation_number',

        'designation_date',

        'project_name',

        'remarks',

    ];

    protected $casts = [

        'designation_date' => 'date',

    ];

    public function deliveryReceipt()
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    public function items()
    {
        return $this->hasMany(SupplyDesignationItem::class);
    }
}