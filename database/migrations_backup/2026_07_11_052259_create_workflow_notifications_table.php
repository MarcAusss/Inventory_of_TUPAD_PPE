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
        Schema::create('workflow_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipient_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('province_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('call_off_id')
                ->nullable()
                ->constrained('call_offs')
                ->nullOnDelete();

            $table->foreignId('delivery_receipt_id')
                ->nullable()
                ->constrained('delivery_receipts')
                ->nullOnDelete();

            $table->string('type', 100);

            $table->string('title');

            $table->text('message');

            $table->string('reference_type')
                ->nullable();

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->enum('status', [
                'Unread',
                'Read',
                'Resolved',
            ])->default('Unread');

            $table->timestamp('read_at')
                ->nullable();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'recipient_user_id',
                'status',
            ], 'workflow_notification_recipient_status_index');

            $table->index([
                'type',
                'created_at',
            ], 'workflow_notification_type_date_index');

            $table->index([
                'reference_type',
                'reference_id',
            ], 'workflow_notification_reference_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_notifications');
    }
};