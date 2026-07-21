<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReceiptDocument extends Model
{
    protected $fillable = ['delivery_receipt_id', 'original_name', 'file_path', 'mime_type', 'file_size'];

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }
}
