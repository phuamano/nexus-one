<?php

declare(strict_types=1);

namespace App\Services\Finanzas;

use App\Enums\Compras\PurchaseStatus;
use App\Enums\Finanzas\AccountPayableStatus;
use App\Models\Compras\Purchase;
use App\Models\Finanzas\AccountPayable;
use Illuminate\Validation\ValidationException;

class AccountPayableService
{
    public function createFromPurchase(
        Purchase $purchase
    ): AccountPayable {

        if ($purchase->status !== PurchaseStatus::CONFIRMED) {
            throw ValidationException::withMessages([
                'purchase' =>
                    'Solo se pueden generar cuentas por pagar para compras confirmadas.',
            ]);
        }

        $existing = AccountPayable::where(
            'purchase_id',
            $purchase->id
        )->first();

        if ($existing) {
            return $existing;
        }

        return AccountPayable::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'issue_date' => $purchase->purchase_date,
            'amount' => $purchase->total,
            'paid_amount' => 0,
            'balance' => $purchase->total,
            'status' => AccountPayableStatus::PENDING,
            'notes' =>
                "Cuenta por pagar de compra {$purchase->reference}",
        ]);
    }
}
