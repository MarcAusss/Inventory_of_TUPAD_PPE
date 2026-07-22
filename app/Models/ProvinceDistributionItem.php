<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'province_distribution_id',
    'item_id',
    'quantity',
])]
class ProvinceDistributionItem extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'province_distribution_id' => 'integer',
            'item_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function provinceDistribution(): BelongsTo
    {
        return $this->belongsTo(
            ProvinceDistribution::class,
            'province_distribution_id',
            'id'
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_id',
            'id'
        );
    }
}