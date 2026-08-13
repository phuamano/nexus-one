<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\FinancialMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinancialMovementService
{
    public function income(
        FinancialAccount $account,
        float $amount,
        ?User $user = null,
        ?string $reference = null,
        ?string $description = null,
        ?string $movementDate = null,
        ?Model $referenceModel = null,
    ): FinancialMovement {
        return $this->createMovement(
            $account,
            FinancialMovementType::INCOME,
            FinancialMovementDirection::IN,
            $amount,
            $user,
            $reference,
            $description,
            $movementDate,
            $referenceModel,
        );
    }

    public function expense(
        FinancialAccount $account,
        float $amount,
        ?User $user = null,
        ?string $reference = null,
        ?string $description = null,
        ?string $movementDate = null,
        ?Model $referenceModel = null,
    ): FinancialMovement {
        if ((float) $account->current_balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Saldo insuficiente en %s. Disponible: %s, solicitado: %s.',
                    $account->name,
                    $account->current_balance,
                    $amount
                ),
            ]);
        }

        return $this->createMovement(
            $account,
            FinancialMovementType::EXPENSE,
            FinancialMovementDirection::OUT,
            $amount,
            $user,
            $reference,
            $description,
            $movementDate,
            $referenceModel,
        );
    }

    private function createMovement(
        FinancialAccount $account,
        FinancialMovementType $type,
        FinancialMovementDirection $direction,
        float $amount,
        ?User $user,
        ?string $reference,
        ?string $description,
        ?string $movementDate,
        ?Model $referenceModel,
    ): FinancialMovement {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto debe ser mayor que cero.',
            ]);
        }

        return DB::transaction(function () use (
            $account,
            $type,
            $direction,
            $amount,
            $user,
            $reference,
            $description,
            $movementDate,
            $referenceModel,
        ) {
            $movement = FinancialMovement::create([
                'financial_account_id' => $account->id,
                'user_id' => $user?->id,
                'type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'reference_type' => $referenceModel?->getMorphClass(),
                'reference_id' => $referenceModel?->getKey(),
                'movement_date' => $movementDate ?? now()->toDateString(),
                'reference' => $reference,
                'description' => $description,
            ]);

            if ($direction === FinancialMovementDirection::IN) {
                $account->increment(
                    'current_balance',
                    $amount
                );
            } else {
                $account->decrement(
                    'current_balance',
                    $amount
                );
            }

            return $movement->fresh([
                'account',
                'user',
                'reference',
            ]);
        });
    }
}
