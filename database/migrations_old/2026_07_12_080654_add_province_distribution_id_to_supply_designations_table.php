<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'supply_designations',
            function (Blueprint $table): void {
                $table->foreignId(
                    'province_distribution_id'
                )
                    ->nullable()
                    ->after('delivery_receipt_id')
                    ->constrained(
                        'province_distributions'
                    )
                    ->restrictOnDelete();

                $table->index(
                    [
                        'province_id',
                        'province_distribution_id',
                        'designation_date',
                    ],
                    'supply_designation_calloff_date_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'supply_designations',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'supply_designation_calloff_date_index'
                );

                $table->dropConstrainedForeignId(
                    'province_distribution_id'
                );
            }
        );
    }
};