<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Compras\PurchaseStatus;
use App\Enums\Finanzas\AccountPayableStatus;
use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Company;
use App\Models\Compras\Purchase;
use App\Models\Compras\Supplier;
use App\Models\Compras\Tax;
use App\Models\Finanzas\AccountPayable;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Models\Finanzas\PayablePayment;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PayablePaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_make_partial_and_final_payments_for_account_payable(): void
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

        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name' => 'Proveedor Test',
            'legal_name' => 'Proveedor Test S.A.C.',
            'tax_id' => '20111111111',
            'email' => 'proveedor@test.com',
            'phone' => '999999999',
            'address' => 'Av. Test 123',
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => Warehouse::create([
                'company_id' => $company->id,
                'name' => 'Almacén Test',
                'code' => 'ALM-TEST',
                'address' => 'Av. Test 123',
                'is_active' => true,
            ])->id,
            'user_id' => $user->id,
            'purchase_date' => '2026-08-13',
            'reference' => 'PURCHASE-001',
            'status' => PurchaseStatus::CONFIRMED->value,
            'subtotal' => 100,
            'tax' => 18,
            'total' => 118,
        ]);

        $accountPayable = AccountPayable::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchase->id,
            'issue_date' => '2026-08-13',
            'due_date' => '2026-09-13',
            'amount' => 118,
            'paid_amount' => 0,
            'balance' => 118,
            'status' => AccountPayableStatus::PENDING->value,
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

        // Pago parcial
        $response = $this->postJson(
            "/api/account-payables/{$accountPayable->id}/payments",
            [
                'financial_account_id' => $financialAccount->id,
                'amount' => 50,
                'payment_method' => 'bank_transfer',
                'reference' => 'PAYMENT-001',
                'notes' => 'Pago parcial',
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Pago registrado correctamente.');

        $accountPayable->refresh();
        $financialAccount->refresh();

        $this->assertSame('50.00', (string) $accountPayable->paid_amount);
        $this->assertSame('68.00', (string) $accountPayable->balance);
        $this->assertSame(
            AccountPayableStatus::PARTIAL,
            $accountPayable->status
        );

        $this->assertSame('450.00', (string) $financialAccount->current_balance);

        $payment = PayablePayment::query()->first();

        $this->assertNotNull($payment);

        $this->assertDatabaseHas('financial_movements', [
            'financial_account_id' => $financialAccount->id,
            'user_id' => $user->id,
            'type' => FinancialMovementType::EXPENSE->value,
            'direction' => FinancialMovementDirection::OUT->value,
            'amount' => 50,
            'reference' => 'PAYMENT-001',
            'reference_type' => PayablePayment::class,
            'reference_id' => $payment->id,
        ]);

        // Pago final
        $response = $this->postJson(
            "/api/account-payables/{$accountPayable->id}/payments",
            [
                'financial_account_id' => $financialAccount->id,
                'amount' => 68,
                'payment_method' => 'bank_transfer',
                'reference' => 'PAYMENT-002',
                'notes' => 'Pago final',
            ]
        );

        $response->assertCreated();

        $accountPayable->refresh();
        $financialAccount->refresh();

        $this->assertSame('118.00', (string) $accountPayable->paid_amount);
        $this->assertSame('0.00', (string) $accountPayable->balance);
        $this->assertSame(
            AccountPayableStatus::PAID,
            $accountPayable->status
        );

        $this->assertSame('382.00', (string) $financialAccount->current_balance);

        $this->assertDatabaseCount('payable_payments', 2);
        $this->assertDatabaseCount('financial_movements', 2);
    }
}
