<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;


#[Fillable([
    'name',
    'category',
    'type',
    'size',
    'brand',
    'unit',
    'unit_cost',
])]
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
    protected $table = 'items';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */



    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
        ];
    }

    public function provinceDistributionItems(): HasMany
    {
        return $this->hasMany(
            ProvinceDistributionItem::class,
            'item_id',
            'id'
        );
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