<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'role_id',
    'province_id',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Call-Offs assigned by this user.
     */
    public function assignedCallOffs(): HasMany
    {
        return $this->hasMany(CallOff::class, 'assigned_by');
    }

    /**
     * Call-Offs approved by this user.
     */
    public function approvedCallOffs(): HasMany
    {
        return $this->hasMany(CallOff::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isSupply(): bool
    {
        return $this->role?->name === Role::SUPPLY;
    }

    public function isTssd(): bool
    {
        return $this->role?->name === Role::TSSD;
    }

    public function isProvincial(): bool
    {
        return $this->role?->name === Role::PROVINCIAL;
    }

    public function isAccounting(): bool
    {
        return $this->role?->name === Role::ACCOUNTING;
    }

    /*
    |--------------------------------------------------------------------------
    | Province Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the user belongs to a province.
     */
    public function hasProvince(): bool
    {
        return ! is_null($this->province_id);
    }

    /**
     * Get the province name safely.
     */
    public function provinceName(): ?string
    {
        return $this->province?->name;
    }

    /**
     * Get the province ID safely.
     */
    public function provinceId(): ?int
    {
        return $this->province_id;
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Only TSSD manages users.
     */
    public function canManageUsers(): bool
    {
        return $this->isTssd();
    }

    /**
     * Supply can manage procurement.
     */
    public function canManageProcurement(): bool
    {
        return $this->isSupply();
    }

    /**
     * TSSD can manage distributions and call-offs.
     */
    public function canManageDistribution(): bool
    {
        return $this->isTssd();
    }

    /**
     * Provincial Office can receive deliveries and manage inventory.
     */
    public function canManageInventory(): bool
    {
        return $this->isProvincial();
    }

    /**
     * Accounting has read-only access.
     */
    public function isReadOnly(): bool
    {
        return $this->isAccounting();
    }
}