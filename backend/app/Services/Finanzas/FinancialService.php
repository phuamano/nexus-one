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

class FinancialService
{
    public function income(
        FinancialAccount $account,
        float $amount,
        ?User $user = null,
        ?string $reference = null,
        ?string $notes = null,
        ?Model $referenceModel = null
    ): FinancialMovement {
        return DB::transaction(function () use (
            $account,
            $amount,
            $user,
            $reference,
            $notes,
            $referenceModel
        ) {
            return $this->createMovement(
                $account,
                $amount,
                FinancialMovementType::INCOME,
                FinancialMovementDirection::IN,
                $user,
                $reference,
                $notes,
                $referenceModel,
            );
        });
    }

    public function expense(
        FinancialAccount $account,
        float $amount,
        ?User $user = null,
        ?string $reference = null,
        ?string $notes = null,
        ?Model $referenceModel = null
    ): FinancialMovement {
        return DB::transaction(function () use (
            $account,
            $amount,
            $user,
            $reference,
            $notes,
            $referenceModel
        ) {
            $this->validateAmount($amount);

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
                $amount,
                FinancialMovementType::EXPENSE,
                FinancialMovementDirection::OUT,
                $user,
                $reference,
                $notes,
                $referenceModel,
            );
        });
    }

    public function transfer(
        FinancialAccount $from,
        FinancialAccount $to,
        float $amount,
        ?User $user = null,
        ?string $reference = null,
        ?string $notes = null,
        ?Model $referenceModel = null
    ): void {
        DB::transaction(function () use (
            $from,
            $to,
            $amount,
            $user,
            $reference,
            $notes,
            $referenceModel
        ) {
            $this->validateAmount($amount);

            if ($from->id === $to->id) {
                throw ValidationException::withMessages([
                    'account' => 'La cuenta de origen y destino deben ser diferentes.',
                ]);
            }

            if ((float) $from->current_balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'Saldo insuficiente en %s. Disponible: %s, solicitado: %s.',
                        $from->name,
                        $from->current_balance,
                        $amount
                    ),
                ]);
            }

            $this->createMovement(
                $from,
                $amount,
                FinancialMovementType::TRANSFER,
                FinancialMovementDirection::OUT,
                $user,
                $reference ?? 'TRANSFER_OUT',
                $notes,
                $referenceModel,
            );

            $this->createMovement(
                $to,
                $amount,
                FinancialMovementType::TRANSFER,
                FinancialMovementDirection::IN,
                $user,
                $reference ?? 'TRANSFER_IN',
                $notes,
                $referenceModel,
            );
        });
    }

    public function adjustment(
        FinancialAccount $account,
        float $amount,
        FinancialMovementDirection $direction,
        ?User $user = null,
        ?string $reference = null,
        ?string $notes = null,
        ?Model $referenceModel = null
    ): FinancialMovement {
        return DB::transaction(function () use (
            $account,
            $amount,
            $direction,
            $user,
            $reference,
            $notes,
            $referenceModel
        ) {
            $this->validateAmount($amount);

            if (
                $direction === FinancialMovementDirection::OUT &&
                (float) $account->current_balance < $amount
            ) {
                throw ValidationException::withMessages([
                    'amount' => 'El saldo de la cuenta no permite realizar este ajuste.',
                ]);
            }

            return $this->createMovement(
                $account,
                $amount,
                FinancialMovementType::ADJUSTMENT,
                $direction,
                $user,
                $reference,
                $notes,
                $referenceModel,
            );
        });
    }

    private function createMovement(
        FinancialAccount $account,
        float $amount,
        FinancialMovementType $type,
        FinancialMovementDirection $direction,
        ?User $user = null,
        ?string $reference = null,
        ?string $notes = null,
        ?Model $referenceModel = null
    ): FinancialMovement {
        $this->validateAmount($amount);

        $movement = FinancialMovement::create([
            'financial_account_id' => $account->id,
            'user_id' => $user?->id,
            'type' => $type->value,
            'direction' => $direction->value,
            'amount' => $amount,
            'movement_date' => now()->toDateString(),
            'reference' => $reference,
            'reference_type' => $referenceModel?->getMorphClass(),
            'reference_id' => $referenceModel?->getKey(),
            'notes' => $notes,
        ]);

        $balanceChange = $direction === FinancialMovementDirection::IN
            ? $amount
            : -$amount;

        $account->increment('current_balance', $balanceChange);

        return $movement;
    }

    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto debe ser mayor que cero.',
            ]);
        }
    }
}
