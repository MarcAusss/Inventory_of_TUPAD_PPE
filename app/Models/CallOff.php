<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'call_off_number',
        'call_off_date',
        'assigned_by',
        'assigned_at',
        'approved_by',
        'approved_at',
        'approval_document',
        'remarks',
        'status',
    ];

    protected $casts = [
        'call_off_date' => 'date',
        'assigned_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}