<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
