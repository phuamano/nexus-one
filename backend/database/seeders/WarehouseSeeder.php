<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();

        Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Almacén Principal',
            'code' => 'ALM-001',
            'address' => 'Av. Principal 123',
            'description' => 'Almacén principal de la empresa',
            'is_active' => true,
        ]);
    }
}
