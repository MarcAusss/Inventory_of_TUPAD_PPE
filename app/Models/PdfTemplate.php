<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfTemplate extends Model
{
    public const REPORT_TYPES = [
        'purchase_order_summary',
        'call_off_summary',
        'tssd_distribution_summary',
        'delivery_receipt_summary',
        'project_designation_summary',
        'provincial_inventory_ledger',
        'accounting_inventory_ledger',
    ];

    protected $fillable = [
        'template_name',
        'report_type',
        'original_filename',
        'pdf_path',
        'file_size',
        'page_count',
        'file_hash',
        'version',
        'is_active',
        'description',
        'uploaded_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'page_count' => 'integer',
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function scopeForReport(
        Builder $query,
        string $reportType
    ): Builder {
        return $query->where(
            'report_type',
            $reportType
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    /**
     * Dropdown options for assigning the PDF.
     *
     * @return array<string, string>
     */
    public static function reportTypeOptions(): array
    {
        return [
            'purchase_order_summary' => 'Purchase Order Summary',

            'call_off_summary' => 'Call-Off Summary',

            'tssd_distribution_summary' => 'TSSD Provincial Distribution Summary',

            'delivery_receipt_summary' => 'Delivery Receipt Summary',

            'project_designation_summary' => 'Project PPE Designation Summary',

            'provincial_inventory_ledger' => 'Provincial Inventory Ledger',

            'accounting_inventory_ledger' => 'Accounting Inventory Ledger',
        ];
    }

    public function reportTypeLabel(): string
    {
        return static::reportTypeOptions()[
            $this->report_type
        ] ?? ucwords(
            str_replace(
                '_',
                ' ',
                $this->report_type
            )
        );
    }

    public function formattedFileSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1_048_576) {
            return number_format(
                $bytes / 1_048_576,
                2
            ).' MB';
        }

        if ($bytes >= 1_024) {
            return number_format(
                $bytes / 1_024,
                2
            ).' KB';
        }

        return number_format($bytes).' bytes';
    }

    public function versionLabel(): string
    {
        return 'Version '.$this->version;
    }
}
