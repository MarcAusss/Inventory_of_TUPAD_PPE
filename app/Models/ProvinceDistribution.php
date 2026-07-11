<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProvinceDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'tssd_distribution_batch_id',
        'province_id',
        'scheduled_delivery_date',
        'place_of_delivery',
        'status',
        'received_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_delivery_date' => 'date',
            'received_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function distributionBatch(): BelongsTo
    {
        return $this->belongsTo(
            TssdDistributionBatch::class,
            'tssd_distribution_batch_id'
        );
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(
            Province::class
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            ProvinceDistributionItem::class,
            'province_distribution_id'
        );
    }

    public function deliveryReceipt(): HasOne
    {
        return $this->hasOne(
            DeliveryReceipt::class,
            'province_distribution_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Accessors
    |--------------------------------------------------------------------------
    |
    | These are accessors instead of methods named callOff() or
    | purchaseOrder(). This avoids Laravel treating normal helper methods as
    | Eloquent relationships when used through property access.
    |
    */

    public function getCallOffRecordAttribute(): ?CallOff
    {
        if (! $this->relationLoaded('distributionBatch')) {
            $this->load(
                'distributionBatch.callOff'
            );
        }

        return $this->distributionBatch?->callOff;
    }

    public function getPurchaseOrderRecordAttribute(): ?PurchaseOrder
    {
        if (! $this->relationLoaded('distributionBatch')) {
            $this->load(
                'distributionBatch.purchaseOrder'
            );
        }

        return $this->distributionBatch?->purchaseOrder;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function belongsToProvince(
        int $provinceId
    ): bool {
        return (int) $this->province_id
            === $provinceId;
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isForDelivery(): bool
    {
        return $this->status === 'For Delivery';
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status
            === 'Partially Received';
    }

    public function isReceived(): bool
    {
        return $this->status === 'Received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    public function canBeReceived(): bool
    {
        return in_array(
            $this->status,
            [
                'Approved',
                'For Delivery',
                'Partially Received',
            ],
            true
        );
    }

    public function totalQuantity(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items
                ->sum('quantity');
        }

        return (int) $this->items()
            ->sum('quantity');
    }
}
