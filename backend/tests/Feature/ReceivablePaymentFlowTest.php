<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Finanzas\AccountReceivableStatus;
use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Enums\Ventas\SaleStatus;
use App\Models\Company;
use App\Models\Finanzas\AccountReceivable;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Models\Finanzas\ReceivablePayment;
use App\Models\User;
use App\Models\Ventas\Customer;
use App\Models\Ventas\Sale;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReceivablePaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_make_partial_and_final_payments_for_account_receivable(): void
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

        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Cliente Test',
            'legal_name' => 'Cliente Test S.A.C.',
            'tax_id' => '20111111111',
            'email' => 'cliente@test.com',
            'phone' => '999999999',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'name' => 'Almacén Test',
            'code' => 'ALM-TEST',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'sale_date' => '2026-08-13 10:00:00',
            'reference' => 'SALE-001',
            'status' => SaleStatus::CONFIRMED->value,
            'subtotal' => 100,
            'tax' => 18,
            'total' => 118,
        ]);

        $accountReceivable = AccountReceivable::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'issue_date' => '2026-08-13',
            'due_date' => '2026-09-13',
            'amount' => 118,
            'paid_amount' => 0,
            'balance' => 118,
            'status' => AccountReceivableStatus::PENDING->value,
        ]);

        $financialAccount = FinancialAccount::create([
            'company_id' => $company->id,
            'name' => 'Banco Test',
            'code' => 'BANK-TEST',
            'type' => 'bank',
            'currency' => 'PEN',
            'initial_balance' => 500,
            'current_balance' => 500,
            'is_active' => true,
        ]);

        // Cobro parcial
        $response = $this->postJson(
            "/api/account-receivables/{$accountReceivable->id}/payments",
            [
                'financial_account_id' => $financialAccount->id,
                'amount' => 50,
                'payment_method' => 'bank_transfer',
                'reference' => 'RECEIPT-001',
                'notes' => 'Cobro parcial',
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'Pago registrado correctamente.'
            );

        $accountReceivable->refresh();
        $financialAccount->refresh();

        $this->assertSame(
            '50.00',
            (string) $accountReceivable->paid_amount
        );

        $this->assertSame(
            '68.00',
            (string) $accountReceivable->balance
        );

        $this->assertSame(
            AccountReceivableStatus::PARTIAL,
            $accountReceivable->status
        );

        $this->assertSame(
            '550.00',
            (string) $financialAccount->current_balance
        );

        $payment = ReceivablePayment::query()->first();

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('financial_movements', [
            'financial_account_id' => $financialAccount->id,
            'user_id' => $user->id,
            'type' => FinancialMovementType::INCOME->value,
            'direction' => FinancialMovementDirection::IN->value,
            'amount' => 50,
            'reference' => 'RECEIPT-001',
            'reference_type' => ReceivablePayment::class,
            'reference_id' => $payment->id,
        ]);

        // Cobro final
        $response = $this->postJson(
            "/api/account-receivables/{$accountReceivable->id}/payments",
            [
                'financial_account_id' => $financialAccount->id,
                'amount' => 68,
                'payment_method' => 'bank_transfer',
                'reference' => 'RECEIPT-002',
                'notes' => 'Cobro final',
            ]
        );

        $response->assertCreated();

        $accountReceivable->refresh();
        $financialAccount->refresh();

        $this->assertSame(
            '118.00',
            (string) $accountReceivable->paid_amount
        );

        $this->assertSame(
            '0.00',
            (string) $accountReceivable->balance
        );

        $this->assertSame(
            AccountReceivableStatus::PAID,
            $accountReceivable->status
        );

        $this->assertSame(
            '618.00',
            (string) $financialAccount->current_balance
        );

        $this->assertDatabaseCount('receivable_payments', 2);
        $this->assertDatabaseCount('financial_movements', 2);
    }
}
