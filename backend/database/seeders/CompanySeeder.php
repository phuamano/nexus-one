<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Nexus Demo',
            'legal_name' => 'Nexus Demo S.A.C.',
            'tax_id' => '20123456789',
            'email' => 'empresa@nexus.test',
            'phone' => '999999999',
            'website' => 'https://nexus.test',
            'timezone' => 'America/Lima',
            'locale' => 'es_PE',
            'currency' => 'PEN',
            'status' => 'active',
        ]);
    }
}
