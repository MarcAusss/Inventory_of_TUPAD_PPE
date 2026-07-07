<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvincialInventory extends Model
{
    protected $fillable = [

        'province_id',

        'item_id',

        'quantity',

    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}