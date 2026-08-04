<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed reference data, users, Purchase Orders, and the complete PPE
     * distribution workflow in dependency order.
     */
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            ProvinceAddressSeeder::class,
            RoleSeeder::class,
        ]);

        // UserSeeder itself is intentionally unchanged. On a fresh database it
        // still creates the standard accounts; on an existing database we do
        // not recreate/overwrite users when refreshing demo data.
        if (! User::query()->exists()) {
            $this->call(UserSeeder::class);
        }

        $this->call([
            SupplierSeeder::class,
            ItemSeeder::class,
            PurchaseOrderSeeder::class,
            DistributionWorkflowSeeder::class,
        ]);
    }
}
