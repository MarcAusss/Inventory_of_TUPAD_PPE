<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * MySQL uses the existing unique index to support the foreign key.
         * Therefore, remove the foreign key before removing the unique index.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'province_distribution_id',
                ]);
            }
        );

        /*
         * Remove the one-receipt-per-allocation restriction.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'delivery_receipt_province_distribution_unique'
                );
            }
        );

        /*
         * Add a normal non-unique index for efficient allocation queries.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->index(
                    'province_distribution_id',
                    'delivery_receipt_province_distribution_index'
                );
            }
        );

        /*
         * Restore the foreign key without restoring uniqueness.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->foreign(
                    'province_distribution_id',
                    'delivery_receipts_province_distribution_id_foreign'
                )
                    ->references('id')
                    ->on('province_distributions')
                    ->restrictOnDelete();
            }
        );
    }

    public function down(): void
    {
        /*
         * Do not restore the old unique constraint when multiple receipts
         * already exist for one provincial allocation.
         */
        $duplicateExists = DB::table(
            'delivery_receipts'
        )
            ->select('province_distribution_id')
            ->whereNotNull('province_distribution_id')
            ->groupBy('province_distribution_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateExists) {
            throw new \RuntimeException(
                'Cannot restore the one-receipt-per-allocation constraint '
                .'because one or more provincial allocations already have '
                .'multiple Delivery Receipts.'
            );
        }

        /*
         * Drop the foreign key before replacing its supporting index.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->dropForeign(
                    'delivery_receipts_province_distribution_id_foreign'
                );
            }
        );

        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'delivery_receipt_province_distribution_index'
                );
            }
        );

        /*
         * Restore the original unique index.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->unique(
                    'province_distribution_id',
                    'delivery_receipt_province_distribution_unique'
                );
            }
        );

        /*
         * Restore the foreign key.
         */
        Schema::table(
            'delivery_receipts',
            function (Blueprint $table): void {
                $table->foreign(
                    'province_distribution_id',
                    'delivery_receipts_province_distribution_id_foreign'
                )
                    ->references('id')
                    ->on('province_distributions')
                    ->restrictOnDelete();
            }
        );
    }
};