<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReceiptItem extends Model
{
    protected $fillable = [
        'delivery_receipt_id',
        'province_distribution_item_id',
        'item_id',

        /*
         * Legacy quantity mirrors received_quantity.
         */
        'quantity',

        'assigned_quantity',
        'received_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'assigned_quantity' => 'integer',
            'received_quantity' => 'integer',
        ];
    }

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryReceipt::class
        );
    }

    public function provinceDistributionItem(): BelongsTo
    {
        return $this->belongsTo(
            ProvinceDistributionItem::class
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class
        );
    }

    public function shortageQuantity(): int
    {
        return max(
            0,
            $this->assigned_quantity
                - $this->received_quantity
        );
    }

    public function hasDiscrepancy(): bool
    {
        return $this->assigned_quantity
            !== $this->received_quantity;
    }
}
