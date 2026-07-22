<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_receipts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('province_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('dr_number')->unique();

            $table->date('delivery_date');

            $table->string('received_by');

            $table->text('remarks')->nullable();

            $table->enum('status', [
                'Pending',
                'Received',
            ])->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_receipts');
    }
};