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
        Schema::create('suppliers', function (Blueprint $table) {

            $table->id();

            $table->string('supplier_name');
            $table->string('contact_person');

            $table->string('contact_number', 20);
            $table->string('email')->nullable();

            $table->text('address');

            $table->text('remarks')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('supplier_name');
            $table->index('contact_person');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};