<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('items')
            ->whereRaw(
                "LOWER(REPLACE(REPLACE(item_name, ' ', ''), '-', '')) IN (?, ?)",
                [
                    'longsleeve',
                    'longsleeves',
                ]
            )
            ->update([
                'item_name' => 'Longsleeve',
            ]);
    }

    public function down(): void
    {
        DB::table('items')
            ->whereRaw(
                "LOWER(REPLACE(REPLACE(item_name, ' ', ''), '-', '')) = ?",
                [
                    'longsleeve',
                ]
            )
            ->update([
                'item_name' => 'Long Sleeve',
            ]);
    }
};
