<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyDesignationItem extends Model
{
    protected $fillable = [

        'supply_designation_id',

        'item_id',

        'quantity',

    ];

    public function supplyDesignation()
    {
        return $this->belongsTo(SupplyDesignation::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}