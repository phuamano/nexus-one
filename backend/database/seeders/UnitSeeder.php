<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();

        Unit::create([
            'company_id' => $company->id,
            'name' => 'Unidad',
            'symbol' => 'UND',
            'description' => 'Unidad de medida para productos',
            'is_active' => true,
        ]);
    }
}
