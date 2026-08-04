<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Supply Unit',
                'description' => 'Creates Purchase Orders and reviews/approves Call-Off requests.',
            ],
            [
                'name' => 'TSSD Unit',
                'description' => 'Allocates PPE to Provincial Offices and monitors PPE movement system-wide.',
            ],
            [
                'name' => 'Provincial Office',
                'description' => 'Receives PPE through Delivery Receipts and distributes available PPE to projects.',
            ],
            [
                'name' => 'Accounting Unit',
                'description' => 'Read-only monitoring and reporting access.',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
