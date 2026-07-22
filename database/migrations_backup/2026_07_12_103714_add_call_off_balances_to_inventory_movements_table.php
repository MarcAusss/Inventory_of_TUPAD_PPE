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
                $table->unsignedInteger(
                    'call_off_balance_before'
                )
                    ->nullable()
                    ->after('balance_after');

                $table->unsignedInteger(
                    'call_off_balance_after'
                )
                    ->nullable()
                    ->after('call_off_balance_before');

                $table->index(
                    [
                        'province_distribution_id',
                        'item_id',
                        'call_off_balance_after',
                    ],
                    'inventory_movement_calloff_balance_index'
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
                    'inventory_movement_calloff_balance_index'
                );

                $table->dropColumn([
                    'call_off_balance_before',
                    'call_off_balance_after',
                ]);
            }
        );
    }
};