<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow multiple projects to use the same PPE item
     * from one Delivery Receipt.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Add a dedicated index for the foreign key
        |--------------------------------------------------------------------------
        |
        | MySQL currently uses the old unique composite index to support
        | the delivery_receipt_id foreign key. We must create another index
        | beginning with delivery_receipt_id before dropping that unique index.
        |
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->index(
                    'delivery_receipt_id',
                    'inventory_movement_delivery_receipt_fk_index'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Step 2: Remove the incorrect uniqueness rule
        |--------------------------------------------------------------------------
        |
        | The old rule prevents this valid workflow:
        |
        | DR 32 + Item 1 + Project A + OUT
        | DR 32 + Item 1 + Project B + OUT
        |
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'inventory_movement_receipt_item_unique'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Step 3: Add normal indexes for Delivery Receipt reporting
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->index(
                    [
                        'delivery_receipt_id',
                        'item_id',
                        'movement_type',
                    ],
                    'inventory_movement_receipt_item_index'
                );

                $table->index(
                    [
                        'delivery_receipt_id',
                        'movement_date',
                    ],
                    'inventory_movement_receipt_date_index'
                );
            }
        );
    }

    /**
     * Restore the previous database structure.
     *
     * Warning:
     * Rolling back can fail if multiple projects already use the same
     * Delivery Receipt and PPE item.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove the normal reporting indexes
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'inventory_movement_receipt_item_index'
                );

                $table->dropIndex(
                    'inventory_movement_receipt_date_index'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Restore the old unique index
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->unique(
                    [
                        'delivery_receipt_id',
                        'item_id',
                        'movement_type',
                    ],
                    'inventory_movement_receipt_item_unique'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Remove the temporary foreign-key support index
        |--------------------------------------------------------------------------
        |
        | The restored unique index now starts with delivery_receipt_id,
        | so it can support the foreign key again.
        |
        */

        Schema::table(
            'inventory_movements',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'inventory_movement_delivery_receipt_fk_index'
                );
            }
        );
    }
};