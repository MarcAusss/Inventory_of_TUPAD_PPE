<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceAddressSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            'Albay' => '4F Ayala Malls, Capantawan, Legazpi City, Albay',

            'Camarines Norte' =>
                '2F Tanzo Bldg., Itomang, Talisay, Camarines Norte',

            'Camarines Sur' =>
                'DOLE bldg., City Hall Compound, Camarines Sur',

            'Catanduanes' =>
                'Llantino Bldg., Conception, Virac, Catanduanes',

            'Masbate' =>
                '2F Sanchez Bldg., Crossing Quezon St., Masbate City, Masbate',

            'Sorsogon' =>
                'DOLE bldg., City Hall Complex, Cabid-an, Sorsogon City, Sorsogon',
        ];

        foreach ($addresses as $provinceName => $address) {
            Province::query()
                ->where('name', $provinceName)
                ->update([
                    'address' => $address,
                ]);
        }
    }
}