<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'tssd_distribution_batch_id',
        'purchase_order_id',
        'call_off_number',
        'call_off_date',
        'assigned_by',
        'assigned_at',
        'nefa_title',
        'print_total_amount',
        'print_margin_top',
        'print_margin_right',
        'print_margin_bottom',
        'print_margin_left',
        'approved_by',
        'approved_at',
        'approval_document',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'call_off_date' => 'date',
            'assigned_at' => 'datetime',
            'approved_at' => 'datetime',
            'print_total_amount' => 'decimal:2',
            'print_margin_top' => 'decimal:2',
            'print_margin_right' => 'decimal:2',
            'print_margin_bottom' => 'decimal:2',
            'print_margin_left' => 'decimal:2',
        ];
    }

    public function distributionBatch(): BelongsTo
    {
        return $this->belongsTo(
            TssdDistributionBatch::class,
            'tssd_distribution_batch_id'
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            'Pending',
            'Rejected',
        ], true);
    }
}
