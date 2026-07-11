<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'created_by',
        'po_number',
        'po_date',
        'nefa_number',
        'total_amount',
        'document',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'po_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Legacy item-level distribution records.
     */
    public function tssdDistributions(): HasMany
    {
        return $this->hasMany(TSSDDistribution::class);
    }

    /**
     * New normalized distribution batches.
     */
    public function distributionBatches(): HasMany
    {
        return $this->hasMany(TssdDistributionBatch::class);
    }

    public function deliveryReceipts(): HasMany
    {
        return $this->hasMany(DeliveryReceipt::class);
    }

    /**
     * Temporary legacy relationship.
     *
     * This will be replaced after Call-Offs are connected to distribution
     * batches.
     */
    public function callOff(): HasOne
    {
        return $this->hasOne(CallOff::class);
    }
}