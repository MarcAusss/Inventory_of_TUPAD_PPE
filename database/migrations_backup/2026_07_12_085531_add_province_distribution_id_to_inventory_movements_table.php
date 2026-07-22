<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->foreignId(
                    'province_distribution_id'
                )
                    ->nullable()
                    ->after('created_by')
                    ->constrained(
                        'province_distributions'
                    )
                    ->nullOnDelete();

                $table->index(
                    [
                        'province_distribution_id',
                        'item_id',
                        'movement_date',
                    ],
                    'inventory_movement_calloff_item_date_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'inventory_movement_calloff_item_date_index'
                );

                $table->dropConstrainedForeignId(
                    'province_distribution_id'
                );
            }
        );
    }
};