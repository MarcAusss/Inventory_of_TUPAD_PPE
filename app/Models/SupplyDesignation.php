<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyDesignation extends Model
{
    protected $fillable = [
        'delivery_receipt_id',
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

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
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

    public function scopeForProvince(
        Builder $query,
        int $provinceId
    ): Builder {
        return $query->where(
            'province_id',
            $provinceId
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
            function (Builder $query) use ($search): void {
                $query
                    ->where(
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
                    );
            }
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }
}
