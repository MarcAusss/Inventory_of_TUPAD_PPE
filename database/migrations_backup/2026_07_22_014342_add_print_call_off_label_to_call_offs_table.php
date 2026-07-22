<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasColumn(
                'call_offs',
                'print_call_off_label'
            )
        ) {
            Schema::table('call_offs', function (
                Blueprint $table
            ): void {
                $table->string('print_call_off_label')
                    ->nullable()
                    ->after('nefa_title');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'call_offs',
                'print_call_off_label'
            )
        ) {
            Schema::table('call_offs', function (
                Blueprint $table
            ): void {
                $table->dropColumn(
                    'print_call_off_label'
                );
            });
        }
    }
};