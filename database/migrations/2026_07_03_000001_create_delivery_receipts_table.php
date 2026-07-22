<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_receipts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('province_distribution_id')
                ->nullable()
                ->constrained('province_distributions')
                ->restrictOnDelete();

            // Retained while legacy receiving workflows are still active.
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();

            $table->foreignId('received_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('dr_number')->unique();
            $table->date('delivery_date');
            $table->string('document')->nullable();
            $table->string('received_by');
            $table->string('physical_receiver_name')->nullable();
            $table->text('remarks')->nullable();

            $table->enum('status', ['Pending', 'Received'])->default('Pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(
                'province_distribution_id',
                'delivery_receipt_province_distribution_index'
            );
            $table->index(
                ['province_id', 'delivery_date'],
                'delivery_receipt_province_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_receipts');
    }
};
