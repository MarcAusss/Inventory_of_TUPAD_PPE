<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $casts = [
        'po_date' => 'date',
    ];

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
    public function tssdDistributions()
    {
        return $this->hasMany(TSSDDistribution::class);
    }
    public function deliveryReceipts()
    {
        return $this->hasMany(DeliveryReceipt::class);
    }
}