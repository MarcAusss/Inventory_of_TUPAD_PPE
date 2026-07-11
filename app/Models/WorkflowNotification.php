<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowNotification extends Model
{
    protected $fillable = [
        'recipient_user_id',
        'province_id',
        'call_off_id',
        'delivery_receipt_id',
        'type',
        'title',
        'message',
        'reference_type',
        'reference_id',
        'status',
        'read_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recipient_user_id'
        );
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function callOff(): BelongsTo
    {
        return $this->belongsTo(CallOff::class);
    }

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'recipient_user_id',
            $userId
        );
    }

    public function scopeUnread(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'Unread'
        );
    }

    public function scopeUnresolved(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'Unread',
                'Read',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isUnread(): bool
    {
        return $this->status === 'Unread';
    }

    public function isResolved(): bool
    {
        return $this->status === 'Resolved';
    }

    public function markAsRead(): void
    {
        if ($this->status !== 'Unread') {
            return;
        }

        $this->update([
            'status' => 'Read',
            'read_at' => now(),
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'Resolved',
            'read_at' => $this->read_at ?: now(),
            'resolved_at' => now(),
        ]);
    }
}
