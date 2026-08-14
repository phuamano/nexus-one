<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\FinancialMovements\Pages;

use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Filament\Resources\Finanzas\FinancialMovements\FinancialMovementResource;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Services\Finanzas\FinancialService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFinancialMovement extends CreateRecord
{
    protected static string $resource = FinancialMovementResource::class;

    protected function handleRecordCreation(array $data): FinancialMovement
    {
        $account = FinancialAccount::findOrFail(
            $data['financial_account_id']
        );

        $service = app(FinancialService::class);

        $user = Auth::user();

        $type = FinancialMovementType::from(
            $data['type']
        );

        return match ($type) {

            FinancialMovementType::INCOME =>
            $service->income(
                $account,
                (float) $data['amount'],
                $user,
                $data['reference'] ?? null,
                $data['notes'] ?? null,
            ),

            FinancialMovementType::EXPENSE =>
            $service->expense(
                $account,
                (float) $data['amount'],
                $user,
                $data['reference'] ?? null,
                $data['notes'] ?? null,
            ),

            FinancialMovementType::ADJUSTMENT =>
            $service->adjustment(
                $account,
                (float) $data['amount'],
                FinancialMovementDirection::from(
                    $data['direction']
                ),
                $user,
                $data['reference'] ?? null,
                $data['notes'] ?? null,
            ),

            default => throw new \RuntimeException(
                'Tipo de movimiento no soportado desde Filament.'
            ),
        };
    }
}
