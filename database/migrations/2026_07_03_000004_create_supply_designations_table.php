<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_designations', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('delivery_receipt_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('province_distribution_id')
                ->nullable()
                ->constrained('province_distributions')
                ->restrictOnDelete();
            $table->foreignId('province_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('designation_number')->unique();
            $table->date('designation_date');
            $table->string('project_name');
            $table->string('project_code')->nullable();
            $table->string('project_title')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('number_of_days')->nullable();
            $table->unsignedInteger('number_of_beneficiaries')->nullable();
            $table->string('are_document')->nullable();
            $table->enum('status', ['Draft', 'Completed', 'Cancelled'])
                ->default('Completed');
            $table->timestamp('submitted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(
                ['province_id', 'project_code'],
                'supply_designation_province_project_unique'
            );
            $table->index(
                ['province_id', 'designation_date'],
                'supply_designation_province_date_index'
            );
            $table->index(
                ['province_id', 'province_distribution_id', 'designation_date'],
                'supply_designation_calloff_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_designations');
    }
};
