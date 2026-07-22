<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $requiredColumnsExist = Schema::hasColumns(
            'delivery_receipt_items',
            [
                'quantity',
                'assigned_quantity',
                'received_quantity',
            ]
        );

        if (! $requiredColumnsExist) {
            throw new \RuntimeException(
                'The delivery_receipt_items table is missing one or more required quantity columns.'
            );
        }

        $hasNegativeValues = DB::table(
            'delivery_receipt_items'
        )
            ->where(function ($query): void {
                $query
                    ->where('quantity', '<', 0)
                    ->orWhere(
                        'assigned_quantity',
                        '<',
                        0
                    )
                    ->orWhere(
                        'received_quantity',
                        '<',
                        0
                    );
            })
            ->exists();

        if ($hasNegativeValues) {
            throw new \RuntimeException(
                'Negative Delivery Receipt item quantities exist. Correct them before running this migration.'
            );
        }

        Schema::table(
            'delivery_receipt_items',
            function (Blueprint $table): void {
                $table->unsignedInteger(
                    'quantity'
                )->change();

                $table->unsignedInteger(
                    'assigned_quantity'
                )->change();

                $table->unsignedInteger(
                    'received_quantity'
                )->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'delivery_receipt_items',
            function (Blueprint $table): void {
                $table->integer(
                    'quantity'
                )->change();

                $table->integer(
                    'assigned_quantity'
                )->change();

                $table->integer(
                    'received_quantity'
                )->change();
            }
        );
    }
};