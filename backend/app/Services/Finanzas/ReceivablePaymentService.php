<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Enums\Finanzas\AccountReceivableStatus;
use App\Models\Finanzas\AccountReceivable;
use App\Models\Finanzas\ReceivablePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivablePaymentService
{
    public function pay(
        AccountReceivable $account,
        float $amount,
        string $method,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null,
    ): ReceivablePayment {
        return DB::transaction(function () use (
            $account,
            $amount,
            $method,
            $reference,
            $notes,
            $user,
        ) {

            if ($account->status === AccountReceivableStatus::CANCELLED) {
                throw ValidationException::withMessages([
                    'account' => 'No se pueden registrar pagos en una cuenta cancelada.',
                ]);
            }

            if ($account->status === AccountReceivableStatus::PAID) {
                throw ValidationException::withMessages([
                    'account' => 'La cuenta por cobrar ya está pagada.',
                ]);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'El monto del pago debe ser mayor que cero.',
                ]);
            }

            $balance = (float) $account->balance;

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'El pago no puede superar el saldo pendiente de %s.',
                        number_format($balance, 2)
                    ),
                ]);
            }

            $payment = ReceivablePayment::create([
                'account_receivable_id' => $account->id,
                'user_id' => $user?->id,
                'payment_date' => now(),
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $paidAmount = (float) $account->paid_amount + $amount;
            $newBalance = (float) $account->amount - $paidAmount;

            if ($newBalance <= 0) {
                $newBalance = 0;

                $status = AccountReceivableStatus::PAID;
            } else {
                $status = AccountReceivableStatus::PARTIAL;
            }

            $account->update([
                'paid_amount' => $paidAmount,
                'balance' => $newBalance,
                'status' => $status,
            ]);

            return $payment->load([
                'accountReceivable',
                'user',
            ]);
        });
    }
}
