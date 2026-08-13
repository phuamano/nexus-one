<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Finanzas\AccountPayableStatus;
use App\Enums\Finanzas\AccountReceivableStatus;
use App\Enums\Finanzas\FinancialAccountType;
use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Company;
use App\Models\Finanzas\AccountPayable;
use App\Models\Finanzas\AccountReceivable;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Models\Finanzas\PayablePayment;
use App\Models\Finanzas\ReceivablePayment;
use App\Models\Compras\Purchase;
use App\Models\Compras\Supplier;
use App\Models\User;
use App\Models\Ventas\Customer;
use App\Models\Ventas\Sale;
use App\Models\Warehouse;
use App\Services\Finanzas\FinancialReportService;
use App\Services\Finanzas\FinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Enums\Compras\PurchaseStatus;
use App\Enums\Ventas\SaleStatus;

class FinancialReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(string $name): Customer
    {
        return Customer::create([
            'name' => $name,
            'legal_name' => $name . ' S.A.C.',
            'tax_id' => fake()->unique()->numerify('20###########'),
            'email' => strtolower(str_replace(' ', '.', $name)) . '@test.com',
            'is_active' => true,
        ]);
    }

    private function createSupplier(string $name): Supplier
    {
        return Supplier::create([
            'name' => $name,
            'legal_name' => $name . ' S.A.C.',
            'tax_id' => fake()->unique()->numerify('20###########'),
            'email' => strtolower(str_replace(' ', '.', $name)) . '@test.com',
            'phone' => '999999999',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);
    }

    private function createContext(): array
    {
        $company = Company::create([
            'name' => 'Empresa Test',
            'legal_name' => 'Empresa Test S.A.C.',
            'tax_id' => '20123456789',
            'email' => 'test@example.com',
            'currency' => 'PEN',
            'status' => 'active',
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Usuario Test',
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        Sanctum::actingAs($user);

        return [$company, $user];
    }

    private function createFinancialAccount(
        string $name,
        string $code,
        FinancialAccountType $type,
        float $balance
    ): FinancialAccount {
        return FinancialAccount::create([
            'name' => $name,
            'code' => $code,
            'type' => $type->value,
            'currency' => 'PEN',
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'is_active' => true,
        ]);
    }

    public function test_can_generate_financial_accounts_summary(): void
    {
        [$company, $user] = $this->createContext();

        $this->createFinancialAccount(
            'Caja',
            'CASH-001',
            FinancialAccountType::CASH,
            300
        );

        $this->createFinancialAccount(
            'Banco',
            'BANK-001',
            FinancialAccountType::BANK,
            700
        );

        $this->createFinancialAccount(
            'Cuenta Inactiva',
            'BANK-002',
            FinancialAccountType::BANK,
            500
        )->update([
            'is_active' => false,
        ]);

        $summary = app(FinancialReportService::class)
            ->financialAccountsSummary();

        $this->assertSame(2, $summary['total_accounts']);
        $this->assertSame(300.0, $summary['cash']);
        $this->assertSame(700.0, $summary['bank']);
        $this->assertSame(1000.0, $summary['total_balance']);

        $this->assertCount(2, $summary['accounts']);
    }

    public function test_can_generate_cash_flow_summary(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createFinancialAccount(
            'Banco',
            'BANK-001',
            FinancialAccountType::BANK,
            1000
        );

        FinancialMovement::create([
            'financial_account_id' => $account->id,
            'user_id' => $user->id,
            'type' => FinancialMovementType::INCOME->value,
            'direction' => FinancialMovementDirection::IN->value,
            'amount' => 500,
            'movement_date' => '2026-08-13',
        ]);

        FinancialMovement::create([
            'financial_account_id' => $account->id,
            'user_id' => $user->id,
            'type' => FinancialMovementType::EXPENSE->value,
            'direction' => FinancialMovementDirection::OUT->value,
            'amount' => 200,
            'movement_date' => '2026-08-13',
        ]);

        $summary = app(FinancialReportService::class)
            ->cashFlowSummary();

        $this->assertSame(500.0, $summary['income']);
        $this->assertSame(200.0, $summary['expense']);
        $this->assertSame(300.0, $summary['net']);
    }

    public function test_can_generate_recent_movements(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createFinancialAccount(
            'Banco',
            'BANK-001',
            FinancialAccountType::BANK,
            1000
        );

        foreach ([
                     1 => '2026-08-11',
                     2 => '2026-08-12',
                     3 => '2026-08-13',
                 ] as $index => $date) {
            FinancialMovement::create([
                'financial_account_id' => $account->id,
                'user_id' => $user->id,
                'type' => FinancialMovementType::INCOME->value,
                'direction' => FinancialMovementDirection::IN->value,
                'amount' => $index * 100,
                'movement_date' => $date,
                'reference' => "MOVEMENT-{$index}",
            ]);
        }

        $movements = app(FinancialReportService::class)
            ->recentMovements(2);

        $this->assertCount(2, $movements);

        $this->assertSame('MOVEMENT-3', $movements[0]['reference']);
        $this->assertSame('MOVEMENT-2', $movements[1]['reference']);
    }

    public function test_can_generate_receivables_summary(): void
    {
        [$company, $user] = $this->createContext();

        $customer = $this->createCustomer('Cliente Test');

        $warehouse = Warehouse::create([
            'name' => 'Almacén Test',
            'code' => 'ALM-TEST',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $sales = [];

        foreach (range(1, 4) as $index) {
            $sales[$index] = Sale::create([
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'sale_date' => '2026-08-13',
                'reference' => "SALE-00{$index}",
                'status' => SaleStatus::CONFIRMED->value,
                'subtotal' => 100,
                'tax' => 18,
                'total' => 100 + ($index * 50),
            ]);
        }

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'sale_id' => $sales[1]->id,
            'issue_date' => '2026-08-13',
            'due_date' => '2026-09-13',
            'amount' => 150,
            'paid_amount' => 0,
            'balance' => 150,
            'status' => AccountReceivableStatus::PENDING->value,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'sale_id' => $sales[2]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'amount' => 200,
            'paid_amount' => 50,
            'balance' => 150,
            'status' => AccountReceivableStatus::PARTIAL->value,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'sale_id' => $sales[3]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'amount' => 300,
            'paid_amount' => 300,
            'balance' => 0,
            'status' => AccountReceivableStatus::PAID->value,
        ]);

        AccountReceivable::create([
            'customer_id' => $customer->id,
            'sale_id' => $sales[4]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-09',
            'amount' => 400,
            'paid_amount' => 0,
            'balance' => 400,
            'status' => AccountReceivableStatus::PENDING->value,
        ]);

        $summary = app(FinancialReportService::class)
            ->receivablesSummary();

        $this->assertSame(1050.0, $summary['total']);
        $this->assertSame(350.0, $summary['paid']);
        $this->assertSame(700.0, $summary['balance']);

        $this->assertSame(550.0, $summary['pending']);
        $this->assertSame(150.0, $summary['partial']);

        $this->assertSame(1, $summary['paid_accounts']);
        $this->assertSame(4, $summary['total_accounts']);

        $this->assertSame(2, $summary['overdue']['count']);
        $this->assertSame(550.0, $summary['overdue']['balance']);
    }

    public function test_can_generate_payables_summary(): void
    {
        [$company, $user] = $this->createContext();

        $supplier = $this->createSupplier('Proveedor Test');

        $warehouse = Warehouse::create([
            'name' => 'Almacén Test',
            'code' => 'ALM-TEST',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $purchases = [];

        foreach (range(1, 4) as $index) {
            $purchases[$index] = Purchase::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'purchase_date' => '2026-08-13',
                'reference' => "PURCHASE-00{$index}",
                'status' => PurchaseStatus::CONFIRMED->value,
                'subtotal' => 100,
                'tax' => 18,
                'total' => 100 + ($index * 50),
            ]);
        }

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchases[1]->id,
            'issue_date' => '2026-08-13',
            'due_date' => '2026-09-13',
            'amount' => 150,
            'paid_amount' => 0,
            'balance' => 150,
            'status' => AccountPayableStatus::PENDING->value,
        ]);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchases[2]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'amount' => 200,
            'paid_amount' => 50,
            'balance' => 150,
            'status' => AccountPayableStatus::PARTIAL->value,
        ]);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchases[3]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'amount' => 300,
            'paid_amount' => 300,
            'balance' => 0,
            'status' => AccountPayableStatus::PAID->value,
        ]);

        AccountPayable::create([
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchases[4]->id,
            'issue_date' => '2026-08-01',
            'due_date' => '2026-08-09',
            'amount' => 400,
            'paid_amount' => 0,
            'balance' => 400,
            'status' => AccountPayableStatus::PENDING->value,
        ]);

        $summary = app(FinancialReportService::class)
            ->payablesSummary();

        $this->assertSame(1050.0, $summary['total']);
        $this->assertSame(350.0, $summary['paid']);
        $this->assertSame(700.0, $summary['balance']);

        $this->assertSame(550.0, $summary['pending']);
        $this->assertSame(150.0, $summary['partial']);

        $this->assertSame(1, $summary['paid_accounts']);
        $this->assertSame(4, $summary['total_accounts']);

        $this->assertSame(2, $summary['overdue']['count']);
        $this->assertSame(550.0, $summary['overdue']['balance']);
    }

    public function test_financial_reports_are_isolated_by_company(): void
    {
        [$company, $user] = $this->createContext();

        $account = FinancialAccount::create([
            'name' => 'Banco Empresa 1',
            'code' => 'BANK-001',
            'type' => FinancialAccountType::BANK->value,
            'currency' => 'PEN',
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'is_active' => true,
        ]);

        $service = app(FinancialService::class);

        $service->income(
            $account,
            250,
            $user,
            'COMPANY-1-INCOME'
        );

        $company2 = Company::create([
            'name' => 'Empresa 2',
            'legal_name' => 'Empresa 2 S.A.C.',
            'tax_id' => '20987654321',
            'email' => 'empresa2@test.com',
            'currency' => 'PEN',
            'status' => 'active',
        ]);

        $user2 = User::create([
            'company_id' => $company2->id,
            'name' => 'Usuario Empresa 2',
            'email' => 'user2@test.com',
            'password' => 'password',
        ]);

        Sanctum::actingAs($user2);

        $account2 = FinancialAccount::create([
            'name' => 'Caja Empresa 2',
            'code' => 'CASH-001',
            'type' => FinancialAccountType::CASH->value,
            'currency' => 'PEN',
            'initial_balance' => 500,
            'current_balance' => 500,
            'is_active' => true,
        ]);

        $service->income(
            $account2,
            100,
            $user2,
            'COMPANY-2-INCOME'
        );

        $report = app(FinancialReportService::class);

        $accountsSummary = $report->financialAccountsSummary();
        $cashFlowSummary = $report->cashFlowSummary();

        $this->assertSame(1, $accountsSummary['total_accounts']);

        $this->assertSame(
            600.0,
            $accountsSummary['total_balance']
        );

        $this->assertSame(
            100.0,
            $cashFlowSummary['income']
        );

        $this->assertSame(
            0.0,
            $cashFlowSummary['expense']
        );

        $this->assertSame(
            100.0,
            $cashFlowSummary['net']
        );

        $this->assertSame(
            $account2->id,
            $accountsSummary['accounts'][0]['id']
        );
    }
}
