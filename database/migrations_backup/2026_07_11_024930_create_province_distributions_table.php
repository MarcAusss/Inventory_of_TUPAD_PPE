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
        Schema::create('province_distributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tssd_distribution_batch_id')
                ->constrained('tssd_distribution_batches')
                ->cascadeOnDelete();

            $table->foreignId('province_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('scheduled_delivery_date')->nullable();

            /*
             * This stores a snapshot of the delivery location used when the
             * distribution is created. If the province address changes later,
             * historical records will retain the original delivery location.
             */
            $table->string('place_of_delivery')->nullable();

            $table->enum('status', [
                'Pending',
                'Approved',
                'For Delivery',
                'Partially Received',
                'Received',
                'Cancelled',
            ])->default('Pending');

            $table->timestamp('received_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            /*
             * A province can appear only once inside a single distribution
             * batch.
             */
            $table->unique(
                [
                    'tssd_distribution_batch_id',
                    'province_id',
                ],
                'distribution_batch_province_unique'
            );

            $table->index([
                'province_id',
                'status',
            ], 'province_distribution_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('province_distributions');
    }
};