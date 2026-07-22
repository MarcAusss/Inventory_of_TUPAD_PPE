<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'item_name',
        'label',
        'unit_of_measurement',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'item_id', 'id');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id', 'id');
    }

    public function provinceDistributionItems(): HasMany
    {
        return $this->hasMany(ProvinceDistributionItem::class, 'item_id', 'id');
    }

    public function tssdDistributions(): HasMany
    {
        return $this->hasMany(TSSDDistribution::class, 'item_id', 'id');
    }

    public function deliveryReceiptItems(): HasMany
    {
        return $this->hasMany(DeliveryReceiptItem::class, 'item_id', 'id');
    }

    public function supplyDesignationItems(): HasMany
    {
        return $this->hasMany(SupplyDesignationItem::class, 'item_id', 'id');
    }

    public function provincialInventories(): HasMany
    {
        return $this->hasMany(ProvincialInventory::class, 'item_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $itemQuery) use ($search): void {
            $itemQuery
                ->where('item_name', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('unit_of_measurement', 'like', "%{$search}%");
        });
    }
}
