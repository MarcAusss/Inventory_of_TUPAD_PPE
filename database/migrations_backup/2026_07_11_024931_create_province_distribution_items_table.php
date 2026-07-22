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
        Schema::create('province_distribution_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('province_distribution_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->timestamps();

            /*
             * The same PPE item cannot be added twice to the same provincial
             * allocation.
             */
            $table->unique(
                [
                    'province_distribution_id',
                    'item_id',
                ],
                'province_distribution_item_unique'
            );

            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('province_distribution_items');
    }
};