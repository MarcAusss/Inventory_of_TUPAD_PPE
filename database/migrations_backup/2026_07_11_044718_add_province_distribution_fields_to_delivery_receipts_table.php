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
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->foreignId('province_distribution_id')
                ->nullable()
                ->after('id')
                ->constrained('province_distributions')
                ->restrictOnDelete();

            $table->foreignId('received_by_user_id')
                ->nullable()
                ->after('province_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('physical_receiver_name')
                ->nullable()
                ->after('received_by_user_id');

            $table->string('document')
                ->nullable()
                ->after('delivery_date');

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('status');

            $table->unique(
                'province_distribution_id',
                'delivery_receipt_province_distribution_unique'
            );

            $table->index([
                'province_id',
                'delivery_date',
            ], 'delivery_receipt_province_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->dropUnique(
                'delivery_receipt_province_distribution_unique'
            );

            $table->dropIndex(
                'delivery_receipt_province_date_index'
            );

            $table->dropConstrainedForeignId(
                'province_distribution_id'
            );

            $table->dropConstrainedForeignId(
                'received_by_user_id'
            );

            $table->dropColumn([
                'physical_receiver_name',
                'document',
                'submitted_at',
            ]);
        });
    }
};