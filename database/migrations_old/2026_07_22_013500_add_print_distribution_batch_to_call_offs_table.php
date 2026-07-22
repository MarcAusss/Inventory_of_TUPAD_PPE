<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->string('print_distribution_batch')
                ->nullable()
                ->after('nefa_title');
        });
    }

    public function down(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->dropColumn('print_distribution_batch');
        });
    }
};
