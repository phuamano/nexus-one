<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrFail();

        User::create([
            'company_id' => $company->id,
            'name' => 'Administrador',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
    }
}
