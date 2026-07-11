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
                $table->unsignedInteger('balance_before')
                    ->nullable()
                    ->after('quantity');

                $table->unsignedInteger('balance_after')
                    ->nullable()
                    ->after('balance_before');

                $table->index(
                    [
                        'province_id',
                        'item_id',
                        'balance_after',
                    ],
                    'inventory_movement_balance_index'
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
                    'inventory_movement_balance_index'
                );

                $table->dropColumn([
                    'balance_before',
                    'balance_after',
                ]);
            }
        );
    }
};