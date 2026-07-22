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
        Schema::create('call_offs', function (Blueprint $table) {
            $table->id();

            // Purchase Order
            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            // Call-Off Information
            $table->string('call_off_number')->unique();
            $table->date('call_off_date')->nullable();

            // Assigned by TSSD
            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('assigned_at')->nullable();

            // Approved by Supply Unit
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
    
            // Approval Document
            $table->string('approval_document')->nullable();

            // Remarks
            $table->text('remarks')->nullable();

            // Status
            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Cancelled',
                'Completed',
            ])->default('Pending');

            $table->timestamps();

            // One Purchase Order = One Call-Off
            $table->unique('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_offs');
    }
};