<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ventas\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrFail();

        Customer::create([
            'company_id' => $company->id,
            'name' => 'Cliente Demo',
            'legal_name' => 'Cliente Demo S.A.C.',
            'tax_id' => '20666666666',
            'email' => 'cliente.demo@example.com',
            'phone' => '988888888',
            'address' => 'Av. Clientes 456, Lima',
            'is_active' => true,
        ]);
    }
}
