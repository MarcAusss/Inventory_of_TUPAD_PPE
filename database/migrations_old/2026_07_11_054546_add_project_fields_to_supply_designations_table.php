<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_designations', function (Blueprint $table) {
            $table->foreignId('province_id')
                ->nullable()
                ->after('delivery_receipt_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->after('province_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('project_code')
                ->nullable()
                ->after('designation_number');

            $table->string('project_title')
                ->nullable()
                ->after('project_code');

            $table->string('location')
                ->nullable()
                ->after('project_title');

            $table->unsignedInteger('number_of_days')
                ->nullable()
                ->after('location');

            $table->unsignedInteger('number_of_beneficiaries')
                ->nullable()
                ->after('number_of_days');

            $table->string('are_document')
                ->nullable()
                ->after('number_of_beneficiaries');

            $table->enum('status', [
                'Draft',
                'Completed',
                'Cancelled',
            ])
                ->default('Completed')
                ->after('are_document');

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('status');

            $table->unique(
                [
                    'province_id',
                    'project_code',
                ],
                'supply_designation_province_project_unique'
            );

            $table->index(
                [
                    'province_id',
                    'designation_date',
                ],
                'supply_designation_province_date_index'
            );
        });

        /*
         * Existing records require delivery_receipt_id, but new project
         * designations operate from total provincial inventory. Make the
         * legacy reference optional.
         */
        Schema::table('supply_designations', function (Blueprint $table) {
            $table->foreignId('delivery_receipt_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('supply_designations', function (Blueprint $table) {
            $table->dropUnique(
                'supply_designation_province_project_unique'
            );

            $table->dropIndex(
                'supply_designation_province_date_index'
            );

            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('created_by');

            $table->dropColumn([
                'project_code',
                'project_title',
                'location',
                'number_of_days',
                'number_of_beneficiaries',
                'are_document',
                'status',
                'submitted_at',
            ]);
        });
    }
};