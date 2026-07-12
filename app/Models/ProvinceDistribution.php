<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SupplyDesignation;

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

    public function supplyDesignations(): HasMany
    {
        return $this->hasMany(
            SupplyDesignation::class,
            'province_distribution_id'
        );
    }

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
            ProvinceDistributionItem::class
        );
    }

    /**
     * One provincial allocation may now have multiple
     * Delivery Receipts.
     *
     * Example:
     *
     * CO-001
     * ├── DR-001
     * └── DR-002
     */
    public function deliveryReceipts(): HasMany
    {
        return $this->hasMany(
            DeliveryReceipt::class,
            'province_distribution_id'
        );
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

    public function isReceived(): bool
    {
        return $this->status === 'Received';
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === 'Partially Received';
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

    /**
     * Total quantity allocated for a specific PPE item.
     */
    public function allocatedQuantity(
        int $itemId
    ): int {
        return (int) $this
            ->items
            ->firstWhere(
                'item_id',
                $itemId
            )
                ?->quantity;
    }

    /**
     * Total quantity already received from all
     * Delivery Receipts.
     */
    public function receivedQuantity(
        int $itemId
    ): int {
        return (int) $this
            ->deliveryReceipts
            ->flatMap(
                fn(DeliveryReceipt $receipt) => $receipt->items
            )
            ->where(
                'item_id',
                $itemId
            )
            ->sum(
                'received_quantity'
            );
    }

    /**
     * Remaining quantity that may still be received.
     */
    public function remainingQuantity(
        int $itemId
    ): int {
        return max(
            0,
            $this->allocatedQuantity($itemId)
            - $this->receivedQuantity($itemId)
        );
    }

    /**
     * Determine whether the entire provincial
     * allocation has been received.
     */
    public function isFullyReceived(): bool
    {
        foreach ($this->items as $item) {
            if (
                $this->remainingQuantity(
                    $item->item_id
                ) > 0
            ) {
                return false;
            }
        }

        return true;
    }
}
