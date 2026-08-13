<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Enums\Finanzas\AccountReceivableStatus;
use App\Enums\Ventas\SaleStatus;
use App\Models\Finanzas\AccountReceivable;
use App\Models\Ventas\Sale;
use Illuminate\Validation\ValidationException;

class AccountReceivableService
{
    public function createFromSale(Sale $sale): AccountReceivable
    {
        if ($sale->status !== SaleStatus::CONFIRMED) {
            throw ValidationException::withMessages([
                'sale' => 'Solo se pueden generar cuentas por cobrar para ventas confirmadas.',
            ]);
        }

        $existing = AccountReceivable::where(
            'sale_id',
            $sale->id
        )->first();

        if ($existing) {
            return $existing;
        }

        return AccountReceivable::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'issue_date' => $sale->sale_date,
            'amount' => $sale->total,
            'paid_amount' => 0,
            'balance' => $sale->total,
            'status' => AccountReceivableStatus::PENDING,
            'notes' => "Cuenta por cobrar de venta {$sale->reference}",
        ]);
    }

    public function cancelFromSale(Sale $sale): void
    {
        $account = AccountReceivable::where(
            'sale_id',
            $sale->id
        )->first();

        if (! $account) {
            return;
        }

        if ($account->status === AccountReceivableStatus::CANCELLED) {
            return;
        }

        if ($account->status === AccountReceivableStatus::PARTIAL) {
            throw ValidationException::withMessages([
                'sale' => 'No se puede cancelar la venta porque la cuenta por cobrar tiene pagos registrados.',
            ]);
        }

        if ($account->status === AccountReceivableStatus::PAID) {
            throw ValidationException::withMessages([
                'sale' => 'No se puede cancelar la venta porque la cuenta por cobrar ya está pagada.',
            ]);
        }

        $account->update([
            'status' => AccountReceivableStatus::CANCELLED,
            'balance' => 0,
        ]);
    }
}
