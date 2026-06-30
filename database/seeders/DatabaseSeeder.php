<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            ItemSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}