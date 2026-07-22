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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('province_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Source references.
             */
            $table->foreignId('delivery_receipt_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('supply_designation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * IN  = PPE received by Provincial Office.
             * OUT = PPE distributed to a project.
             * ADJUSTMENT_IN / ADJUSTMENT_OUT are reserved for future
             * authorized inventory corrections.
             */
            $table->enum('movement_type', [
                'IN',
                'OUT',
                'ADJUSTMENT_IN',
                'ADJUSTMENT_OUT',
            ]);

            $table->unsignedInteger('quantity');

            $table->date('movement_date');

            $table->string('reference_number')
                ->nullable();

            $table->string('description')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->index([
                'province_id',
                'movement_date',
            ], 'inventory_movement_province_date_index');

            $table->index([
                'province_id',
                'item_id',
                'movement_date',
            ], 'inventory_movement_item_date_index');

            $table->index([
                'movement_type',
                'movement_date',
            ], 'inventory_movement_type_date_index');

            /*
             * Prevent duplicate movement rows for the same received PPE item.
             */
            $table->unique(
                [
                    'delivery_receipt_id',
                    'item_id',
                    'movement_type',
                ],
                'inventory_movement_receipt_item_unique'
            );

            /*
             * Prevent duplicate movement rows for the same project PPE item.
             */
            $table->unique(
                [
                    'supply_designation_id',
                    'item_id',
                    'movement_type',
                ],
                'inventory_movement_designation_item_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};