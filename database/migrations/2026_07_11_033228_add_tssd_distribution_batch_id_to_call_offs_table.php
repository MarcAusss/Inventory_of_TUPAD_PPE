<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_offs', function (Blueprint $table) {
            $table->foreignId('tssd_distribution_batch_id')
                ->nullable()
                ->after('id')
                ->constrained('tssd_distribution_batches')
                ->cascadeOnDelete();

            $table->unique(
                'tssd_distribution_batch_id',
                'call_off_batch_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('call_offs', function (Blueprint $table) {
            $table->dropUnique('call_off_batch_unique');

            $table->dropConstrainedForeignId(
                'tssd_distribution_batch_id'
            );
        });
    }
};