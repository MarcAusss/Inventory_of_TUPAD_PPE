<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'item_name',
        'label',
        'unit_of_measurement',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */



    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Scope
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, $search): void
    {
        if (!$search) {
            return;
        }

        $query->where('item_name', 'like', "%{$search}%")
            ->orWhere('label', 'like', "%{$search}%")
            ->orWhere('unit_of_measurement', 'like', "%{$search}%");
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'item_id');
    }

    public function tssdDistributions()
    {
        return $this->hasMany(TSSDDistribution::class);
    }
    public function deliveryReceiptItems()
    {
        return $this->hasMany(DeliveryReceiptItem::class);
    }

    public function supplyDesignationItems()
    {
        return $this->hasMany(SupplyDesignationItem::class);
    }
    public function provincialInventories()
    {
        return $this->hasMany(ProvincialInventory::class);
    }
}