<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supply_designations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('delivery_receipt_id')  
                ->constrained()
                ->cascadeOnDelete();

            $table->string('designation_number')->unique();

            $table->date('designation_date');

            $table->string('project_name');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_designations');
    }
};