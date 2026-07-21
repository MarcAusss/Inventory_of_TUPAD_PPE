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
        $provinces = [
            [
                'name' => 'Albay',
                'office_name' => 'DOLE Albay Provincial Office',
                'delivery_address' => '4F Ayala Malls, Capantawan, Legazpi City, Albay',
            ],
            [
                'name' => 'Camarines Norte',
                'office_name' => 'DOLE Camarines Norte Provincial Office',
                'delivery_address' => '2F Tanzo Bldg., Itomang, Talisay, Camarines Norte',
            ],
            [
                'name' => 'Camarines Sur',
                'office_name' => 'DOLE Camarines Sur Provincial Office',
                'delivery_address' => 'DOLE bldg., City Hall Compound, Camarines Sur',
            ],
            [
                'name' => 'Catanduanes',
                'office_name' => 'DOLE Catanduanes Provincial Office',
                'delivery_address' => 'Llantino Bldg., Conception, Virac, Catanduanes',
            ],
            [
                'name' => 'Masbate',
                'office_name' => 'DOLE Masbate Provincial Office',
                'delivery_address' => '2F Sanchez Bldg., Crossing Quezon St., Masbate City, Masbate',
            ],
            [
                'name' => 'Sorsogon',
                'office_name' => 'DOLE Sorsogon Provincial Office',
                'delivery_address' => 'DOLE bldg., City Hall Complex, Cabid-an, Sorsogon City, Sorsogon',
            ],
        ];

        foreach ($provinces as $province) {
            Province::updateOrCreate(
                [
                    'name' => $province['name'],
                ],
                [
                    'office_name' => $province['office_name'],
                    'delivery_address' => $province['delivery_address'],
                ]
            );
        }
    }
}