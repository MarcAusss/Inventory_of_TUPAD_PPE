<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tssd_distribution_batches', function (Blueprint $table): void {
            $table->text('call_off_letter_nefa_title')
                ->nullable()
                ->after('remarks');

            $table->decimal('call_off_letter_total_amount', 15, 2)
                ->nullable()
                ->after('call_off_letter_nefa_title');

            $table->decimal('call_off_letter_margin_top', 5, 2)
                ->default(9)
                ->after('call_off_letter_total_amount');

            $table->decimal('call_off_letter_margin_right', 5, 2)
                ->default(11)
                ->after('call_off_letter_margin_top');

            $table->decimal('call_off_letter_margin_bottom', 5, 2)
                ->default(28)
                ->after('call_off_letter_margin_right');

            $table->decimal('call_off_letter_margin_left', 5, 2)
                ->default(11)
                ->after('call_off_letter_margin_bottom');

            $table->timestamp('call_off_letter_submitted_at')
                ->nullable()
                ->after('call_off_letter_margin_left');

            $table->index(
                'call_off_letter_submitted_at',
                'tssd_batch_letter_submitted_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tssd_distribution_batches', function (Blueprint $table): void {
            $table->dropIndex('tssd_batch_letter_submitted_index');
            $table->dropColumn([
                'call_off_letter_nefa_title',
                'call_off_letter_total_amount',
                'call_off_letter_margin_top',
                'call_off_letter_margin_right',
                'call_off_letter_margin_bottom',
                'call_off_letter_margin_left',
                'call_off_letter_submitted_at',
            ]);
        });
    }
};
