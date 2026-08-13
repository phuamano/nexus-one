<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Finanzas\FinancialAccountType;
use App\Models\Company;
use App\Models\Finanzas\FinancialAccount;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrFail();

        FinancialAccount::updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'CASH-001',
            ],
            [
                'name' => 'Caja General',
                'type' => FinancialAccountType::CASH,
                'currency' => $company->currency,
                'initial_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'notes' => 'Cuenta de efectivo principal.',
            ]
        );

        FinancialAccount::updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'BANK-001',
            ],
            [
                'name' => 'Cuenta Bancaria Principal',
                'type' => FinancialAccountType::BANK,
                'currency' => $company->currency,
                'initial_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'notes' => 'Cuenta bancaria principal de la empresa.',
            ]
        );
    }
}
