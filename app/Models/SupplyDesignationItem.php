<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyDesignationItem extends Model
{
    protected $fillable = [
        'supply_designation_id',
        'item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function supplyDesignation(): BelongsTo
    {
        return $this->belongsTo(
            SupplyDesignation::class
        );
    }

    /**
     * Temporary compatibility alias for older code.
     */
    public function designation(): BelongsTo
    {
        return $this->supplyDesignation();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
