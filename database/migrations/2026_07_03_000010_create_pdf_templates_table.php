<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();

            $table->string('template_name');

            /*
            |--------------------------------------------------------------------------
            | Assigned System Report
            |--------------------------------------------------------------------------
            */

            $table->string('report_type');

            /*
            |--------------------------------------------------------------------------
            | Uploaded PDF
            |--------------------------------------------------------------------------
            */

            $table->string('original_filename');

            $table->string('pdf_path');

            $table->unsignedBigInteger('file_size')
                ->nullable();

            $table->unsignedInteger('page_count')
                ->nullable();

            $table->string('file_hash', 64)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Version Control
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('version')
                ->default(1);

            $table->boolean('is_active')
                ->default(false);

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                [
                    'report_type',
                    'is_active',
                ],
                'pdf_templates_report_active_index'
            );

            $table->unique(
                [
                    'report_type',
                    'version',
                ],
                'pdf_templates_report_version_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_templates');
    }
};