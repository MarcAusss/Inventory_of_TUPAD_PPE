<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_name' => 'ABC Safety Supplies',
                'contact_person' => 'Juan Dela Cruz',
                'contact_number' => '09171234567',
                'email' => 'sales@abcsafety.test',
                'address' => 'Legazpi City, Albay',
                'remarks' => 'Primary demo supplier for the first Purchase Order.',
                'is_active' => true,
            ],
            [
                'supplier_name' => 'Bicol Industrial Trading',
                'contact_person' => 'Maria Santos',
                'contact_number' => '09181234567',
                'email' => 'sales@bicolindustrial.test',
                'address' => 'Naga City, Camarines Sur',
                'remarks' => 'Demo supplier for active distribution workflows.',
                'is_active' => true,
            ],
            [
                'supplier_name' => 'SafeWear Philippines',
                'contact_person' => 'Pedro Cruz',
                'contact_number' => '09221234567',
                'email' => 'sales@safewear.test',
                'address' => 'Sorsogon City, Sorsogon',
                'remarks' => 'Demo supplier for an undistributed Purchase Order.',
                'is_active' => true,
            ],
            [
                'supplier_name' => 'Guardian PPE Trading',
                'contact_person' => 'Ana Reyes',
                'contact_number' => '09331234567',
                'email' => 'sales@guardianppe.test',
                'address' => 'Daet, Camarines Norte',
                'remarks' => 'Additional active supplier for manual testing.',
                'is_active' => true,
            ],
            [
                'supplier_name' => 'Prime Industrial Supply',
                'contact_person' => 'Jose Mendoza',
                'contact_number' => '09441234567',
                'email' => 'sales@primeindustrial.test',
                'address' => 'Masbate City, Masbate',
                'remarks' => 'Additional active supplier for manual testing.',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['supplier_name' => $supplier['supplier_name']],
                $supplier
            );
        }
    }
}
