<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'item_name',
        'label',
        'unit_of_measurement',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Read and store every Longsleeves spelling under one canonical item name.
     */
    protected function itemName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): string => self::canonicalItemName(
                (string) $value
            ),
            set: fn (mixed $value): string => self::canonicalItemName(
                (string) $value
            )
        );
    }

    /**
     * Return the canonical user-facing PPE item name.
     */
    public static function canonicalItemName(string $value): string
    {
        $itemName = trim($value);

        $normalizedName = strtolower(
            str_replace(
                [
                    ' ',
                    '-',
                    '_',
                ],
                '',
                $itemName
            )
        );

        return in_array(
            $normalizedName,
            [
                'longsleeve',
                'longsleeves',
            ],
            true
        )
            ? 'Longsleeves'
            : $itemName;
    }

    /**
     * Build a stable display key so paired PPE sizes always appear in the
     * requested order: Medium before Large and US9 before US10.
     */
    public static function displaySortKey(
        ?string $itemName,
        ?string $label = null
    ): string {
        $canonicalName = self::canonicalItemName((string) $itemName);
        $normalizedName = strtolower(
            str_replace(
                [
                    ' ',
                    '-',
                    '_',
                ],
                '',
                $canonicalName
            )
        );
        $normalizedLabel = strtolower(trim((string) $label));

        $sizeOrder = match ($normalizedName) {
            'longsleeves' => match ($normalizedLabel) {
                'medium', 'm' => 10,
                'large', 'l' => 20,
                default => 90,
            },
            'rubberboots' => match ($normalizedLabel) {
                'us9', '9' => 10,
                'us10', '10' => 20,
                default => 90,
            },
            default => 50,
        };

        return strtolower($canonicalName)
            . '|'
            . str_pad((string) $sizeOrder, 2, '0', STR_PAD_LEFT)
            . '|'
            . $normalizedLabel;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'item_id', 'id');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id', 'id');
    }

    public function provinceDistributionItems(): HasMany
    {
        return $this->hasMany(ProvinceDistributionItem::class, 'item_id', 'id');
    }

    public function tssdDistributions(): HasMany
    {
        return $this->hasMany(TSSDDistribution::class, 'item_id', 'id');
    }

    public function deliveryReceiptItems(): HasMany
    {
        return $this->hasMany(DeliveryReceiptItem::class, 'item_id', 'id');
    }

    public function supplyDesignationItems(): HasMany
    {
        return $this->hasMany(SupplyDesignationItem::class, 'item_id', 'id');
    }

    public function provincialInventories(): HasMany
    {
        return $this->hasMany(ProvincialInventory::class, 'item_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */


    /**
     * Order PPE items for user-facing lists and tables.
     */
    public function scopeOrderForDisplay(Builder $query): Builder
    {
        $normalizedName = "LOWER(REPLACE(REPLACE(REPLACE(items.item_name, ' ', ''), '-', ''), '_', ''))";

        return $query
            ->orderByRaw($normalizedName)
            ->orderByRaw(
                "CASE
                    WHEN {$normalizedName} IN ('longsleeve', 'longsleeves') THEN
                        CASE LOWER(COALESCE(items.label, ''))
                            WHEN 'medium' THEN 10
                            WHEN 'm' THEN 10
                            WHEN 'large' THEN 20
                            WHEN 'l' THEN 20
                            ELSE 90
                        END
                    WHEN {$normalizedName} = 'rubberboots' THEN
                        CASE LOWER(COALESCE(items.label, ''))
                            WHEN 'us9' THEN 10
                            WHEN '9' THEN 10
                            WHEN 'us10' THEN 20
                            WHEN '10' THEN 20
                            ELSE 90
                        END
                    ELSE 50
                END"
            )
            ->orderByRaw("LOWER(COALESCE(items.label, ''))");
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $itemQuery) use ($search): void {
            $itemQuery
                ->where('item_name', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('unit_of_measurement', 'like', "%{$search}%");
        });
    }
}
