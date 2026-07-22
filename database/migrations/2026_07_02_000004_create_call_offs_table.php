<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_offs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('tssd_distribution_batch_id')
                ->nullable()
                ->constrained('tssd_distribution_batches')
                ->cascadeOnDelete();

            // Retained until all legacy purchase-order Call-Off code is removed.
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();

            $table->string('call_off_number')->unique();
            $table->text('nefa_title')->nullable();
            $table->string('print_call_off_label')->nullable();
            $table->string('print_distribution_batch')->nullable();
            $table->decimal('print_total_amount', 15, 2)->nullable();
            $table->decimal('print_margin_top', 5, 2)->default(9);
            $table->decimal('print_margin_right', 5, 2)->default(11);
            $table->decimal('print_margin_bottom', 5, 2)->default(28);
            $table->decimal('print_margin_left', 5, 2)->default(11);
            $table->date('call_off_date')->nullable();

            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_document')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Cancelled',
                'Completed',
            ])->default('Pending');
            $table->timestamps();

            $table->unique('tssd_distribution_batch_id', 'call_off_batch_unique');
            $table->index('purchase_order_id', 'call_offs_purchase_order_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_offs');
    }
};
