<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();

        ProductCategory::create([
            'company_id' => $company->id,
            'name' => 'Categoría Demo',
            'description' => 'Categoría de prueba para desarrollo',
            'is_active' => true,
        ]);
    }
}
