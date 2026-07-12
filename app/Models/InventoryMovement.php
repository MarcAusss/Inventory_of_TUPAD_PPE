<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'province_id',
        'item_id',
        'created_by',

        /*
         * Exact Call-Off allocation source.
         */
        'province_distribution_id',

        'delivery_receipt_id',
        'supply_designation_id',

        'movement_type',
        'quantity',

        /*
         * These remain province-wide pooled inventory balances.
         */
        'balance_before',
        'balance_after',

        'movement_date',
        'reference_number',
        'description',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'movement_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function province(): BelongsTo
    {
        return $this->belongsTo(
            Province::class
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function provinceDistribution(): BelongsTo
    {
        return $this->belongsTo(
            ProvinceDistribution::class
        );
    }

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryReceipt::class
        );
    }

    public function supplyDesignation(): BelongsTo
    {
        return $this->belongsTo(
            SupplyDesignation::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForProvince(
        Builder $query,
        int $provinceId
    ): Builder {
        return $query->where(
            'province_id',
            $provinceId
        );
    }

    public function scopeForCallOffAllocation(
        Builder $query,
        int $provinceDistributionId
    ): Builder {
        return $query->where(
            'province_distribution_id',
            $provinceDistributionId
        );
    }

    public function scopeForYear(
        Builder $query,
        int $year
    ): Builder {
        return $query->whereYear(
            'movement_date',
            $year
        );
    }

    public function scopeStockIn(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'movement_type',
            [
                'IN',
                'ADJUSTMENT_IN',
            ]
        );
    }

    public function scopeStockOut(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'movement_type',
            [
                'OUT',
                'ADJUSTMENT_OUT',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isStockIn(): bool
    {
        return in_array(
            $this->movement_type,
            [
                'IN',
                'ADJUSTMENT_IN',
            ],
            true
        );
    }

    public function isStockOut(): bool
    {
        return in_array(
            $this->movement_type,
            [
                'OUT',
                'ADJUSTMENT_OUT',
            ],
            true
        );
    }

    public function signedQuantity(): int
    {
        return $this->isStockIn()
            ? (int) $this->quantity
            : -((int) $this->quantity);
    }

    public function callOff(): ?CallOff
    {
        return $this
            ->provinceDistribution
            ?->distributionBatch
            ?->callOff;
    }

    public function purchaseOrder(): ?PurchaseOrder
    {
        return $this
            ->provinceDistribution
            ?->distributionBatch
            ?->purchaseOrder;
    }

    public function supplier(): ?Supplier
    {
        return $this
            ->provinceDistribution
            ?->distributionBatch
            ?->purchaseOrder
            ?->supplier;
    }
}
