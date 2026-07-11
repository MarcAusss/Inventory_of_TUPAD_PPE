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
        Schema::create('tssd_distribution_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('distribution_date');

            $table->enum('status', [
                'Draft',
                'Submitted',
                'Call-Off Assigned',
                'Pending Approval',
                'Approved',
                'Partially Received',
                'Completed',
                'Cancelled',
            ])->default('Draft');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index([
                'purchase_order_id',
                'distribution_date',
            ], 'distribution_batch_po_date_index');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tssd_distribution_batches');
    }
};