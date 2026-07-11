<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvincialInventory extends Model
{
    protected $fillable = [
        'province_id',
        'item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
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

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (! $search) {
            return $query;
        }

        return $query->whereHas(
            'item',
            function (Builder $itemQuery) use ($search): void {
                $itemQuery->where(
                    function (Builder $query) use ($search): void {
                        $query
                            ->where(
                                'item_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'label',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'unit_of_measurement',
                                'like',
                                "%{$search}%"
                            );
                    }
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }
}
