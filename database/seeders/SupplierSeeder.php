<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::insert([

            [
                'supplier_name' => 'ABC Safety Supplies',
                'contact_person' => 'Juan Dela Cruz',
                'contact_number' => '09171234567',
                'email' => 'abc@gmail.com',
                'address' => 'Legazpi City, Albay',
                'remarks' => 'Primary PPE Supplier',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'supplier_name' => 'Bicol Industrial Trading',
                'contact_person' => 'Maria Santos',
                'contact_number' => '09181234567',
                'email' => 'bicol@gmail.com',
                'address' => 'Naga City, Camarines Sur',
                'remarks' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'supplier_name' => 'SafeWear Philippines',
                'contact_person' => 'Pedro Cruz',
                'contact_number' => '09221234567',
                'email' => 'safewear@gmail.com',
                'address' => 'Sorsogon City',
                'remarks' => 'Boots and Gloves',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'supplier_name' => 'Guardian PPE Trading',
                'contact_person' => 'Ana Reyes',
                'contact_number' => '09331234567',
                'email' => 'guardian@gmail.com',
                'address' => 'Daet, Camarines Norte',
                'remarks' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'supplier_name' => 'Prime Industrial Supply',
                'contact_person' => 'Jose Mendoza',
                'contact_number' => '09441234567',
                'email' => 'prime@gmail.com',
                'address' => 'Masbate City',
                'remarks' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}