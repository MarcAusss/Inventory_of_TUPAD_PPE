<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('province_distribution_id')
                ->nullable()
                ->constrained('province_distributions')
                ->nullOnDelete();
            $table->foreignId('delivery_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supply_designation_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('movement_type', [
                'IN',
                'OUT',
                'ADJUSTMENT_IN',
                'ADJUSTMENT_OUT',
            ]);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance_before')->nullable();
            $table->unsignedInteger('balance_after')->nullable();
            $table->unsignedInteger('call_off_balance_before')->nullable();
            $table->unsignedInteger('call_off_balance_after')->nullable();
            $table->date('movement_date');
            $table->string('reference_number')->nullable();
            $table->string('description')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['province_id', 'movement_date'], 'inventory_movement_province_date_index');
            $table->index(['province_id', 'item_id', 'movement_date'], 'inventory_movement_item_date_index');
            $table->index(['movement_type', 'movement_date'], 'inventory_movement_type_date_index');
            $table->index(['province_id', 'item_id', 'balance_after'], 'inventory_movement_balance_index');
            $table->index(
                ['province_distribution_id', 'item_id', 'movement_date'],
                'inventory_movement_calloff_item_date_index'
            );
            $table->index(
                ['province_distribution_id', 'item_id', 'call_off_balance_after'],
                'inventory_movement_calloff_balance_index'
            );
            $table->index('delivery_receipt_id', 'inventory_movement_delivery_receipt_fk_index');
            $table->index(
                ['delivery_receipt_id', 'item_id', 'movement_type'],
                'inventory_movement_receipt_item_index'
            );
            $table->index(
                ['delivery_receipt_id', 'movement_date'],
                'inventory_movement_receipt_date_index'
            );
            $table->unique(
                ['supply_designation_id', 'item_id', 'movement_type'],
                'inventory_movement_designation_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
