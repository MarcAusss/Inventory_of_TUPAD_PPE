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
        if (! Schema::hasColumn('provinces', 'office_name')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->string('office_name')
                    ->nullable()
                    ->after('name');
            });
        }

        if (! Schema::hasColumn('provinces', 'delivery_address')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->text('delivery_address')
                    ->nullable()
                    ->after('office_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('provinces', 'delivery_address')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->dropColumn('delivery_address');
            });
        }

        if (Schema::hasColumn('provinces', 'office_name')) {
            Schema::table('provinces', function (Blueprint $table) {
                $table->dropColumn('office_name');
            });
        }
    }
};