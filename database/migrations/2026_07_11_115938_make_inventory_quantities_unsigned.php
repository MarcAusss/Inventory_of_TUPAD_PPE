<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $negativeInventoryCount = DB::table(
            'provincial_inventories'
        )
            ->where('quantity', '<', 0)
            ->count();

        if ($negativeInventoryCount > 0) {
            throw new \RuntimeException(
                'Negative provincial inventory records exist. '
                .'Correct them before running this migration.'
            );
        }

        $negativeDesignationCount = DB::table(
            'supply_designation_items'
        )
            ->where('quantity', '<', 0)
            ->count();

        if ($negativeDesignationCount > 0) {
            throw new \RuntimeException(
                'Negative supply designation quantities exist. '
                .'Correct them before running this migration.'
            );
        }

        Schema::table(
            'provincial_inventories',
            function (Blueprint $table): void {
                $table->unsignedInteger('quantity')
                    ->default(0)
                    ->change();
            }
        );

        Schema::table(
            'supply_designation_items',
            function (Blueprint $table): void {
                $table->unsignedInteger('quantity')
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'supply_designation_items',
            function (Blueprint $table): void {
                $table->integer('quantity')
                    ->change();
            }
        );

        Schema::table(
            'provincial_inventories',
            function (Blueprint $table): void {
                $table->integer('quantity')
                    ->default(0)
                    ->change();
            }
        );
    }
};