<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceAddressSeeder extends Seeder
{
    /**
     * Keep Provincial Office names and delivery addresses synchronized.
     * This seeder is safe to run independently after ProvinceSeeder.
     */
    public function run(): void
    {
        $provinces = (new ProvinceSeeder())->provinces();

        foreach ($provinces as $province) {
            Province::query()
                ->where('name', $province['name'])
                ->update([
                    'office_name' => $province['office_name'],
                    'delivery_address' => $province['delivery_address'],
                ]);
        }
    }
}
