<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Finanzas\FinancialAccountType;
use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Company;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Models\User;
use App\Services\Finanzas\FinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinancialServiceTest extends TestCase
{
    use RefreshDatabase;

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

    private function createAccount(
        string $name,
        string $code,
        float $balance = 500,
    ): FinancialAccount {
        return FinancialAccount::create([
            'name' => $name,
            'code' => $code,
            'type' => FinancialAccountType::BANK->value,
            'currency' => 'PEN',
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'is_active' => true,
        ]);
    }

    public function test_can_register_income(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $movement = $service->income(
            $account,
            150,
            $user,
            'INCOME-001',
            'Ingreso de prueba'
        );

        $account->refresh();

        $this->assertSame('650.00', (string) $account->current_balance);

        $this->assertSame(
            FinancialMovementType::INCOME,
            $movement->type
        );

        $this->assertSame(
            FinancialMovementDirection::IN,
            $movement->direction
        );

        $this->assertSame('150.00', (string) $movement->amount);

        $this->assertDatabaseHas('financial_movements', [
            'financial_account_id' => $account->id,
            'user_id' => $user->id,
            'type' => FinancialMovementType::INCOME->value,
            'direction' => FinancialMovementDirection::IN->value,
            'amount' => 150,
            'reference' => 'INCOME-001',
        ]);
    }

    public function test_can_register_expense(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $movement = $service->expense(
            $account,
            120,
            $user,
            'EXPENSE-001',
            'Egreso de prueba'
        );

        $account->refresh();

        $this->assertSame('380.00', (string) $account->current_balance);

        $this->assertSame(
            FinancialMovementType::EXPENSE,
            $movement->type
        );

        $this->assertSame(
            FinancialMovementDirection::OUT,
            $movement->direction
        );

        $this->assertSame('120.00', (string) $movement->amount);
    }

    public function test_can_transfer_between_financial_accounts(): void
    {
        [$company, $user] = $this->createContext();

        $from = $this->createAccount(
            'Banco Origen',
            'BANK-001',
            500
        );

        $to = $this->createAccount(
            'Caja Destino',
            'CASH-001',
            200
        );

        $service = app(FinancialService::class);

        $service->transfer(
            $from,
            $to,
            150,
            $user,
            'TRANSFER-001',
            'Transferencia de prueba'
        );

        $from->refresh();
        $to->refresh();

        $this->assertSame('350.00', (string) $from->current_balance);
        $this->assertSame('350.00', (string) $to->current_balance);

        $this->assertDatabaseCount('financial_movements', 2);

        $this->assertDatabaseHas('financial_movements', [
            'financial_account_id' => $from->id,
            'type' => FinancialMovementType::TRANSFER->value,
            'direction' => FinancialMovementDirection::OUT->value,
            'amount' => 150,
            'reference' => 'TRANSFER-001',
        ]);

        $this->assertDatabaseHas('financial_movements', [
            'financial_account_id' => $to->id,
            'type' => FinancialMovementType::TRANSFER->value,
            'direction' => FinancialMovementDirection::IN->value,
            'amount' => 150,
            'reference' => 'TRANSFER-001',
        ]);
    }

    public function test_can_make_incoming_adjustment(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $movement = $service->adjustment(
            $account,
            75,
            FinancialMovementDirection::IN,
            $user,
            'ADJUSTMENT-001',
            'Ajuste positivo'
        );

        $account->refresh();

        $this->assertSame('575.00', (string) $account->current_balance);

        $this->assertSame(
            FinancialMovementType::ADJUSTMENT,
            $movement->type
        );

        $this->assertSame(
            FinancialMovementDirection::IN,
            $movement->direction
        );
    }

    public function test_can_make_outgoing_adjustment(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $movement = $service->adjustment(
            $account,
            75,
            FinancialMovementDirection::OUT,
            $user,
            'ADJUSTMENT-002',
            'Ajuste negativo'
        );

        $account->refresh();

        $this->assertSame('425.00', (string) $account->current_balance);

        $this->assertSame(
            FinancialMovementType::ADJUSTMENT,
            $movement->type
        );

        $this->assertSame(
            FinancialMovementDirection::OUT,
            $movement->direction
        );
    }

    public function test_cannot_expense_more_than_available_balance(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            100
        );

        $service = app(FinancialService::class);

        $this->expectException(ValidationException::class);

        $service->expense(
            $account,
            101,
            $user
        );
    }

    public function test_cannot_transfer_more_than_available_balance(): void
    {
        [$company, $user] = $this->createContext();

        $from = $this->createAccount(
            'Banco Origen',
            'BANK-001',
            100
        );

        $to = $this->createAccount(
            'Banco Destino',
            'BANK-002',
            200
        );

        $service = app(FinancialService::class);

        $this->expectException(ValidationException::class);

        $service->transfer(
            $from,
            $to,
            101,
            $user
        );
    }

    public function test_cannot_transfer_to_same_account(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $this->expectException(ValidationException::class);

        $service->transfer(
            $account,
            $account,
            100,
            $user
        );
    }

    public function test_cannot_make_outgoing_adjustment_without_balance(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            100
        );

        $service = app(FinancialService::class);

        $this->expectException(ValidationException::class);

        $service->adjustment(
            $account,
            101,
            FinancialMovementDirection::OUT,
            $user
        );
    }

    public function test_cannot_register_zero_or_negative_amount(): void
    {
        [$company, $user] = $this->createContext();

        $account = $this->createAccount(
            'Banco Test',
            'BANK-001',
            500
        );

        $service = app(FinancialService::class);

        $this->expectException(ValidationException::class);

        $service->income(
            $account,
            0,
            $user
        );
    }
}
