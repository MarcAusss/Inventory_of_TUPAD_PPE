<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProvinceDistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_distribution_id',
        'item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
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
            ProvinceDistribution::class
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function deliveryReceiptItems(): HasMany
    {
        return $this->hasMany(
            DeliveryReceiptItem::class
        );
    }
}