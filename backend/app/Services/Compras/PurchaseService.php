<?php

declare(strict_types=1);

namespace App\Services\Compras;

use App\Enums\Compras\PurchaseStatus;
use App\Models\Compras\Purchase;
use App\Models\Compras\PurchaseItem;
use App\Models\Compras\Tax;
use App\Models\InventoryMovement;
use App\Services\Finanzas\AccountPayableService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        private readonly AccountPayableService $accountPayableService,
    )
    {
    }

    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {

            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'La compra debe tener al menos un producto.',
                ]);
            }

            $taxes = Tax::query()
                ->whereIn('id', collect($items)->pluck('tax_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $tax = 0;

            foreach ($items as $item) {

                $taxModel = $taxes->get($item['tax_id']);

                if (!$taxModel) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los impuestos seleccionados no es válido o está inactivo.',
                    ]);
                }

                $itemSubtotal =
                    (float)$item['quantity']
                    * (float)$item['unit_cost'];

                $itemTax =
                    $itemSubtotal
                    * ((float)$taxModel->rate / 100);

                $subtotal += $itemSubtotal;
                $tax += $itemTax;
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'],
                'purchase_date' => $data['purchase_date'],
                'reference' => $data['reference'],
                'status' => PurchaseStatus::DRAFT,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {

                $taxModel = $taxes->get($item['tax_id']);

                $itemSubtotal =
                    (float)$item['quantity']
                    * (float)$item['unit_cost'];

                $itemTax =
                    $itemSubtotal
                    * ((float)$taxModel->rate / 100);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'tax_id' => $item['tax_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemSubtotal + $itemTax,
                ]);
            }

            return $purchase->load([
                'supplier',
                'warehouse',
                'user',
                'purchaseItems.product',
                'purchaseItems.tax',
                'inventoryMovements',
            ]);
        });
    }

    /** * Actualiza una compra en estado borrador. */
    public function update(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {

            if ($purchase->status !== PurchaseStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden actualizar compras en estado borrador.',
                ]);
            }

            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'La compra debe tener al menos un producto.',
                ]);
            }

            $subtotal = 0;
            $tax = 0;
            $calculatedItems = [];

            foreach ($items as $item) {

                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                $itemSubtotal = $quantity * $unitCost;

                $itemTax = $itemSubtotal * (
                        (float) \App\Models\Compras\Tax::query()
                            ->findOrFail($item['tax_id'])
                            ->rate / 100
                    );

                $itemTotal = $itemSubtotal + $itemTax;

                $subtotal += $itemSubtotal;
                $tax += $itemTax;

                $calculatedItems[] = [
                    'product_id' => $item['product_id'],
                    'tax_id' => $item['tax_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ];
            }

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'reference' => $data['reference'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'notes' => $data['notes'] ?? null,
            ]);

            $purchase->purchaseItems()->delete();

            foreach ($calculatedItems as $item) {
                $purchase->purchaseItems()->create($item);
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

            $this->accountPayableService
                ->createFromPurchase($purchase);

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
