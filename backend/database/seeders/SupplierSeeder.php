<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Compras\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::firstOrCreate(
            ['tax_id' => '20123456789'],
            [
                'name' => 'Proveedor Demo',
                'legal_name' => 'Proveedor Demo S.A.C.',
                'email' => 'proveedor.demo@example.com',
                'phone' => '999999999',
                'address' => 'Av. Proveedores 123, Lima',
                'is_active' => true,
            ]
        );

        Supplier::firstOrCreate(
            ['tax_id' => '20987654321'],
            [
                'name' => 'Distribuidora Nacional',
                'legal_name' => 'Distribuidora Nacional S.A.C.',
                'email' => 'ventas@distribuidora.demo',
                'phone' => '988888888',
                'address' => 'Av. Industrial 456, Lima',
                'is_active' => true,
            ]
        );
    }
}
