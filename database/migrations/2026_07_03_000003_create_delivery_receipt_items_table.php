<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_receipt_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('province_distribution_item_id')
                ->nullable()
                ->constrained('province_distribution_items')
                ->restrictOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            // quantity remains for compatibility with the legacy workflow.
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('assigned_quantity')->default(0);
            $table->unsignedInteger('received_quantity')->default(0);
            $table->timestamps();

            $table->unique(
                ['delivery_receipt_id', 'province_distribution_item_id'],
                'delivery_receipt_distribution_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_receipt_items');
    }
};
