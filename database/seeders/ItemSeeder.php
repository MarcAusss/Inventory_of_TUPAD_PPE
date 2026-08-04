<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['item_name' => 'Longsleeves', 'label' => 'Medium', 'unit_of_measurement' => 'Piece'],
            ['item_name' => 'Longsleeves', 'label' => 'Large', 'unit_of_measurement' => 'Piece'],
            ['item_name' => 'Bucket Hat', 'label' => null, 'unit_of_measurement' => 'Piece'],
            ['item_name' => 'Rubber Boots', 'label' => 'US9', 'unit_of_measurement' => 'Pair'],
            ['item_name' => 'Rubber Boots', 'label' => 'US10', 'unit_of_measurement' => 'Pair'],
            ['item_name' => 'Hand Gloves', 'label' => null, 'unit_of_measurement' => 'Pair'],
            ['item_name' => 'Mask', 'label' => null, 'unit_of_measurement' => 'Box'],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(
                [
                    'item_name' => $item['item_name'],
                    'label' => $item['label'],
                ],
                [
                    'unit_of_measurement' => $item['unit_of_measurement'],
                    'is_active' => true,
                ]
            );
        }
    }
}
