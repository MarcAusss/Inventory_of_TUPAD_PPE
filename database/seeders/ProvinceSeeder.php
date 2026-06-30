<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Province::insert([
            ['name' => 'Albay'],
            ['name' => 'Camarines Norte'],
            ['name' => 'Camarines Sur'],
            ['name' => 'Catanduanes'],
            ['name' => 'Masbate'],
            ['name' => 'Sorsogon'],
        ]);
    }
}