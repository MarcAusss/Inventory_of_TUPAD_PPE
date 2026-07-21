<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->text('nefa_title')
                ->nullable()
                ->after('call_off_number');
        });
    }

    public function down(): void
    {
        Schema::table('call_offs', function (Blueprint $table): void {
            $table->dropColumn('nefa_title');
        });
    }
};