<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_receipt_items', function (Blueprint $table) {
            $table->foreignId('province_distribution_item_id')
                ->nullable()
                ->after('delivery_receipt_id')
                ->constrained('province_distribution_items')
                ->restrictOnDelete();

            $table->unsignedInteger('assigned_quantity')
                ->default(0)
                ->after('item_id');

            $table->unsignedInteger('received_quantity')
                ->default(0)
                ->after('assigned_quantity');

            $table->unique(
                [
                    'delivery_receipt_id',
                    'province_distribution_item_id',
                ],
                'delivery_receipt_distribution_item_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_receipt_items', function (Blueprint $table) {
            $table->dropUnique(
                'delivery_receipt_distribution_item_unique'
            );

            $table->dropConstrainedForeignId(
                'province_distribution_item_id'
            );

            $table->dropColumn([
                'assigned_quantity',
                'received_quantity',
            ]);
        });
    }
};