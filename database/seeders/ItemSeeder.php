<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::insert([

            [
                'item_name' => 'Long Sleeve',
                'label' => 'Medium',
                'unit_of_measurement' => 'Piece',
                'is_active' => true,
            ],

            [
                'item_name' => 'Long Sleeve',
                'label' => 'Large',
                'unit_of_measurement' => 'Piece',
                'is_active' => true,
            ],

            [
                'item_name' => 'Bucket Hat',
                'label' => null,
                'unit_of_measurement' => 'Piece',
                'is_active' => true,
            ],

            [
                'item_name' => 'Rubber Boots',
                'label' => 'US9',
                'unit_of_measurement' => 'Pair',
                'is_active' => true,
            ],

            [
                'item_name' => 'Rubber Boots',
                'label' => 'US10',
                'unit_of_measurement' => 'Pair',
                'is_active' => true,
            ],

            [
                'item_name' => 'Hand Gloves',
                'label' => null,
                'unit_of_measurement' => 'Pair',
                'is_active' => true,
            ],

            [
                'item_name' => 'Mask',
                'label' => null,
                'unit_of_measurement' => 'Box',
                'is_active' => true,
            ],

        ]);
    }
}