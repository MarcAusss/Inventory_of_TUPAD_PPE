<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            [
                'name' => 'Supply Unit',
                'description' => 'Supply Office',
            ],
            [
                'name' => 'TSSD Unit',
                'description' => 'Distribution',
            ],
            [
                'name' => 'Provincial Office',
                'description' => 'Province User',
            ],
            [
                'name' => 'Accounting Unit',
                'description' => 'Read Only',
            ],
        ]);
    }
}