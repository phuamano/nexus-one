<?php

declare(strict_types=1);

namespace App\Services\Compras;

use App\Enums\Compras\PurchaseStatus;
use App\Models\Compras\Purchase;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function confirm(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {

            $purchase->loadMissing([
                'purchaseItems',
            ]);

            if ($purchase->status !== PurchaseStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden confirmar compras en estado borrador.',
                ]);
            }

            if ($purchase->purchaseItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'La compra debe tener al menos un producto.',
                ]);
            }

            $purchase->update([
                'status' => PurchaseStatus::CONFIRMED,
            ]);

            foreach ($purchase->purchaseItems as $item) {

                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'type' => 'purchase',
                    'direction' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'notes' => $purchase->reference
                        ? "Entrada por compra {$purchase->reference}"
                        : 'Entrada por compra',
                    'user_id' => $purchase->user_id,
                ]);
            }

            return $purchase->fresh([
                'supplier',
                'warehouse',
                'user',
                'purchaseItems.product',
                'purchaseItems.tax',
                'inventoryMovements',
            ]);
        });
    }
}
