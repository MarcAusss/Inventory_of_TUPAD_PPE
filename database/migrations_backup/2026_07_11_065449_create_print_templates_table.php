<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Template Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('report_type')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Official Header
            |--------------------------------------------------------------------------
            */

            $table->string('agency_name')
                ->default('Department of Labor and Employment');

            $table->string('regional_office')
                ->nullable();

            $table->string('office_name')
                ->nullable();

            $table->text('office_address')
                ->nullable();

            $table->string('contact_information')
                ->nullable();

            $table->string('report_title');

            $table->string('report_subtitle')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Header Images
            |--------------------------------------------------------------------------
            */

            $table->string('left_logo')
                ->nullable();

            $table->string('right_logo')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Signatories
            |--------------------------------------------------------------------------
            */

            $table->string('prepared_by_name')
                ->nullable();

            $table->string('prepared_by_position')
                ->nullable();

            $table->string('reviewed_by_name')
                ->nullable();

            $table->string('reviewed_by_position')
                ->nullable();

            $table->string('approved_by_name')
                ->nullable();

            $table->string('approved_by_position')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Print Layout
            |--------------------------------------------------------------------------
            */

            $table->enum('paper_size', [
                'A4',
                'Letter',
                'Legal',
            ])->default('A4');

            $table->enum('orientation', [
                'portrait',
                'landscape',
            ])->default('portrait');

            $table->unsignedInteger('margin_top')
                ->default(15);

            $table->unsignedInteger('margin_right')
                ->default(15);

            $table->unsignedInteger('margin_bottom')
                ->default(15);

            $table->unsignedInteger('margin_left')
                ->default(15);

            /*
            |--------------------------------------------------------------------------
            | Footer
            |--------------------------------------------------------------------------
            */

            $table->text('footer_text')
                ->nullable();

            $table->boolean('show_page_number')
                ->default(true);

            $table->boolean('show_generated_date')
                ->default(true);

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'report_type',
                'is_active',
            ], 'print_template_type_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};