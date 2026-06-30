<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplyRole = Role::where('name', 'Supply Unit')->first();
        $tssdRole = Role::where('name', 'TSSD Unit')->first();
        $accountingRole = Role::where('name', 'Accounting Unit')->first();
        $provinceRole = Role::where('name', 'Provincial Office')->first();

        // Supply Unit
        User::create([
            'name' => 'Supply Unit',
            'username' => 'supply',
            'email' => 'supply@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $supplyRole->id,
            'province_id' => null,
        ]);

        // TSSD Unit
        User::create([
            'name' => 'TSSD Unit',
            'username' => 'tssd',
            'email' => 'tssd@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $tssdRole->id,
            'province_id' => null,
        ]);

        // Accounting Unit
        User::create([
            'name' => 'Accounting Unit',
            'username' => 'accounting',
            'email' => 'accounting@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $accountingRole->id,
            'province_id' => null,
        ]);

        // Provincial Office Users
        $provinces = [
            [
                'province' => 'Albay',
                'username' => 'albay',
                'email' => 'albay@example.com',
            ],
            [
                'province' => 'Camarines Norte',
                'username' => 'camarines_norte',
                'email' => 'camarines_norte@example.com',
            ],
            [
                'province' => 'Camarines Sur',
                'username' => 'camarines_sur',
                'email' => 'camarines_sur@example.com',
            ],
            [
                'province' => 'Catanduanes',
                'username' => 'catanduanes',
                'email' => 'catanduanes@example.com',
            ],
            [
                'province' => 'Masbate',
                'username' => 'masbate',
                'email' => 'masbate@example.com',
            ],
            [
                'province' => 'Sorsogon',
                'username' => 'sorsogon',
                'email' => 'sorsogon@example.com',
            ],
        ];

        foreach ($provinces as $provinceUser) {
            $province = Province::where('name', $provinceUser['province'])->first();

            User::create([
                'name' => $provinceUser['province'] . ' Provincial Office',
                'username' => $provinceUser['username'],
                'email' => $provinceUser['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $provinceRole->id,
                'province_id' => $province->id,
            ]);
        }
    }
}