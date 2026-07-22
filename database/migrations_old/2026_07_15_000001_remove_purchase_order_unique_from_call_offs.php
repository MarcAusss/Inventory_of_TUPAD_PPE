<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the old rule that allowed only one Call-Off
     * for each Purchase Order.
     */
    public function up(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            /*
             * MySQL requires an index for the purchase_order_id
             * foreign key. The existing unique index currently
             * serves as that required index.
             *
             * Create a regular index first before removing the
             * unique index.
             */
            $table->index(
                'purchase_order_id',
                'call_offs_purchase_order_id_index'
            );
        });

        Schema::table('call_offs', function (Blueprint $table): void {
            $table->dropUnique(
                'call_offs_purchase_order_id_unique'
            );
        });
    }

    /**
     * Restore the original one-Call-Off-per-Purchase-Order rule.
     */
    public function down(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->unique(
                'purchase_order_id',
                'call_offs_purchase_order_id_unique'
            );
        });

        Schema::table('call_offs', function (Blueprint $table): void {
            $table->dropIndex(
                'call_offs_purchase_order_id_index'
            );
        });
    }
};