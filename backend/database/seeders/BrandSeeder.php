<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();

        Brand::create([
            'company_id' => $company->id,
            'name' => 'Marca Demo',
            'description' => 'Marca de prueba para desarrollo',
            'is_active' => true,
        ]);
    }
}
