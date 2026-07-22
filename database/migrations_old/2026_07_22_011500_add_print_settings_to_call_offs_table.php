<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->decimal('print_total_amount', 15, 2)
                ->nullable()
                ->after('nefa_title');

            $table->decimal('print_margin_top', 5, 2)
                ->default(9)
                ->after('print_total_amount');

            $table->decimal('print_margin_right', 5, 2)
                ->default(11)
                ->after('print_margin_top');

            $table->decimal('print_margin_bottom', 5, 2)
                ->default(28)
                ->after('print_margin_right');

            $table->decimal('print_margin_left', 5, 2)
                ->default(11)
                ->after('print_margin_bottom');
        });
    }

    public function down(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->dropColumn([
                'print_total_amount',
                'print_margin_top',
                'print_margin_right',
                'print_margin_bottom',
                'print_margin_left',
            ]);
        });
    }
};
