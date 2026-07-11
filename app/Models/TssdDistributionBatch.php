<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TssdDistributionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'created_by',
        'distribution_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function provinceDistributions(): HasMany
    {
        return $this->hasMany(ProvinceDistribution::class);
    }

    public function callOff(): HasOne
    {
        return $this->hasOne(
            CallOff::class,
            'tssd_distribution_batch_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isEditable(): bool
    {
        return in_array($this->status, [
            'Draft',
            'Submitted',
        ], true);
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }
}