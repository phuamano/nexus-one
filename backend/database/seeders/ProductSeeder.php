<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();
        $category = ProductCategory::where('company_id', $company->id)->firstOrFail();
        $brand = Brand::where('company_id', $company->id)->firstOrFail();
        $unit = Unit::where('company_id', $company->id)->firstOrFail();

        Product::create([
            'company_id' => $company->id,
            'product_category_id' => $category->id,
            'name' => 'Producto Demo',
            'sku' => 'PROD-001',
            'barcode' => '775000000001',
            'description' => 'Producto de prueba para desarrollo',
            'cost' => 10.00,
            'price' => 15.00,
            'stock_min' => 5.00,
            'is_active' => true,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
        ]);
    }
}
