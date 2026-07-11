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
        return $this->belongsTo(Province::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            ProvinceDistributionItem::class
        );
    }

    public function deliveryReceipt(): HasOne
    {
        return $this->hasOne(
            DeliveryReceipt::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function belongsToProvince(int $provinceId): bool
    {
        return (int) $this->province_id
            === $provinceId;
    }

    public function isReceived(): bool
    {
        return $this->status === 'Received';
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, [
            'Approved',
            'For Delivery',
            'Partially Received',
        ], true);
    }

    public function callOff(): ?CallOff
    {
        return $this
            ->distributionBatch
            ?->callOff;
    }

    public function purchaseOrder(): ?PurchaseOrder
    {
        return $this
            ->distributionBatch
            ?->purchaseOrder;
    }
}