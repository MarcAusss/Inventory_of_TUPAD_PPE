<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryReceipt extends Model
{
    protected $fillable = [
        'province_distribution_id',
        'purchase_order_id',
        'province_id',
        'received_by_user_id',
        'physical_receiver_name',
        'dr_number',
        'delivery_date',
        'document',
        'received_by',
        'remarks',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'submitted_at' => 'datetime',
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

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(
            Province::class
        );
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by_user_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            DeliveryReceiptItem::class
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DeliveryReceiptDocument::class);
    }

    public function supplyDesignations(): HasMany
    {
        return $this->hasMany(
            SupplyDesignation::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isReceived(): bool
    {
        return $this->status === 'Received';
    }

    public function callOff(): ?CallOff
    {
        return $this
            ->provinceDistribution
            ?->distributionBatch
            ?->callOff;
    }

    public function totalReceivedQuantity(): int
    {
        return (int) $this
            ->items
            ->sum('received_quantity');
    }

    public function hasDiscrepancy(): bool
    {
        return $this
            ->items
            ->contains(
                fn (DeliveryReceiptItem $item): bool =>
                    $item->hasDiscrepancy()
            );
    }
}