<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Compras\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        Tax::firstOrCreate(
            ['code' => 'IGV'],
            [
                'name' => 'IGV',
                'rate' => 18.00,
                'is_active' => true,
            ]
        );

        Tax::firstOrCreate(
            ['code' => 'EXO'],
            [
                'name' => 'Exonerado',
                'rate' => 0.00,
                'is_active' => true,
            ]
        );
    }
}
