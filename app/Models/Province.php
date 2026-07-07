<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name'])] // Allows mass assignment for province names during seeding
class Province extends Model
{
    /**
     * Get the users residing in this province.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tssdDistributions()
    {
        return $this->hasMany(TSSDDistribution::class);
    }
    public function deliveryReceipts()
    {
        return $this->hasMany(DeliveryReceipt::class);
    }
    public function inventories()
    {
        return $this->hasMany(ProvincialInventory::class);
    }
}
