<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyDesignation extends Model
{
    protected $fillable = [
        /*
         * Legacy receipt reference retained for compatibility.
         */
        'delivery_receipt_id',

        /*
         * New source Call-Off allocation reference.
         */
        'province_distribution_id',

        'province_id',
        'created_by',
        'designation_number',
        'designation_date',
        'project_name',
        'project_code',
        'project_title',
        'location',
        'number_of_days',
        'number_of_beneficiaries',
        'are_document',
        'status',
        'submitted_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'designation_date' => 'date',

            'number_of_days' => 'integer',

            'number_of_beneficiaries' => 'integer',

            'submitted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Legacy relationship retained for historical records.
     */
    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryReceipt::class
        );
    }

    /**
     * Source provincial allocation used by this project.
     *
     * This relationship identifies the Call-Off, Purchase Order,
     * supplier, province, and original PPE allocation.
     */
    public function provinceDistribution(): BelongsTo
    {
        return $this->belongsTo(
            ProvinceDistribution::class
        );
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(
            Province::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SupplyDesignationItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
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

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (! $search) {
            return $query;
        }

        return $query->where(
            function (
                Builder $query
            ) use (
                $search
            ): void {
                $query
                    ->where(
                        'designation_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'project_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'project_title',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'location',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'provinceDistribution'
                            .'.distributionBatch'
                            .'.callOff',
                        fn (
                            Builder $callOffQuery
                        ) => $callOffQuery->where(
                            'call_off_number',
                            'like',
                            "%{$search}%"
                        )
                    );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
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

    public function totalDesignatedQuantity(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this
                ->items
                ->sum('quantity');
        }

        return (int) $this
            ->items()
            ->sum('quantity');
    }
}
