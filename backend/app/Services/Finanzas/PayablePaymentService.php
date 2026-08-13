<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Enums\Finanzas\AccountPayableStatus;
use App\Models\Finanzas\AccountPayable;
use App\Models\Finanzas\FinancialAccount;
use App\Models\Finanzas\PayablePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayablePaymentService
{
    public function __construct(
        private readonly FinancialService $financialService,
    ) {
    }
    public function pay(
        AccountPayable $account,
        FinancialAccount $financialAccount,
        float $amount,
        string $method,
        ?string $reference = null,
        ?string $notes = null,
        ?User $user = null,
    ): PayablePayment {

        return DB::transaction(function () use (
            $account,
            $financialAccount,
            $amount,
            $method,
            $reference,
            $notes,
            $user
        ) {

            if ($account->status === AccountPayableStatus::PAID) {
                throw ValidationException::withMessages([
                    'account' =>
                        'La cuenta por pagar ya está pagada.',
                ]);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'El monto del pago debe ser mayor que cero.',
                ]);
            }

            if ($amount > (float) $account->balance) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'El pago no puede superar el saldo pendiente de %s.',
                        $account->balance
                    ),
                ]);
            }

            $paidAmount =
                (float) $account->paid_amount + $amount;

            $newBalance =
                (float) $account->amount - $paidAmount;

            $status = $newBalance <= 0
                ? AccountPayableStatus::PAID
                : AccountPayableStatus::PARTIAL;

            $payment = PayablePayment::create([
                'account_payable_id' => $account->id,
                'user_id' => $user?->id,
                'payment_date' => now(),
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $this->financialService->expense(
                $financialAccount,
                $amount,
                $user,
                $reference,
                $notes,
                $payment,
            );

            $account->update([
                'paid_amount' => $paidAmount,
                'balance' => max(0, $newBalance),
                'status' => $status,
            ]);

            return $payment->fresh([
                'accountPayable',
                'user',
            ]);
        });
    }
}
