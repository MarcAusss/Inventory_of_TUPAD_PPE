<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'office_name',
    'delivery_address',
])]
class Province extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Legacy TSSD allocation records.
     *
     * This relationship is temporarily retained while the old distribution
     * table is being replaced safely.
     */
    public function tssdDistributions(): HasMany
    {
        return $this->hasMany(TSSDDistribution::class);
    }

    public function provinceDistributions(): HasMany
    {
        return $this->hasMany(ProvinceDistribution::class);
    }

    public function deliveryReceipts(): HasMany
    {
        return $this->hasMany(DeliveryReceipt::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(ProvincialInventory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function deliveryLocation(): string
    {
        return $this->delivery_address
            ?: $this->office_name
            ?: $this->name;
    }
}
