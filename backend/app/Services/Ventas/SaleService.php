<?php
declare(strict_types=1);

namespace App\Services\Ventas;

use App\Enums\Ventas\SaleStatus;
use App\Models\InventoryMovement;
use App\Models\Ventas\Sale;
use App\Models\Ventas\SaleItem;
use App\Services\Finanzas\AccountReceivableService;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private readonly AccountReceivableService $accountReceivableService,
        private readonly InventoryService $inventoryService,
    ) {
    }
    /**
     * Crea una venta en estado borrador.
     */
    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            $items = $data['items'] ?? [];

            $this->validateItems($items);

            $subtotal = 0;
            $tax = 0;

            foreach ($items as $item) {
                $subtotal += (float) $item['subtotal'];
                $tax += (float) $item['tax_amount'];
            }

            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'],
                'sale_date' => $data['sale_date'],
                'reference' => $data['reference'] ?? null,
                'status' => SaleStatus::DRAFT,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'tax_id' => $item['tax_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $item['subtotal'],
                    'total' => $item['total'],
                ]);
            }

            return $sale->load([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
                'inventoryMovements',
            ]);
        });
    }

    /**
     * Confirma una venta y genera las salidas de inventario.
     */
    public function confirm(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {

            if ($sale->status !== SaleStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'sale' => 'Solo se pueden confirmar ventas en estado borrador.',
                ]);
            }

            $sale->load([
                'saleItems.product',
                'warehouse',
                'user'
            ]);

            foreach ($sale->saleItems as $item) {
                $this->inventoryService->exit(
                    $item->product,
                    $sale->warehouse,
                    (float) $item->quantity,
                    $sale->user,
                    "Salida por venta {$sale->reference}",
                    $sale,
                );
            }

            $sale->update([
                'status' => SaleStatus::CONFIRMED,
            ]);

            $this->accountReceivableService
                ->createFromSale($sale);



            return $sale->fresh([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
                'inventoryMovements',
            ]);
        });
    }

    /**
     * Cancela una venta confirmada y revierte el inventario.
     */
    public function cancel(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {

            if ($sale->status !== SaleStatus::CONFIRMED) {
                throw ValidationException::withMessages([
                    'sale' => 'Solo se pueden cancelar ventas confirmadas.',
                ]);
            }

            $sale->load('saleItems');

            foreach ($sale->saleItems as $item) {
                $this->inventoryService->return(
                    $item->product,
                    $sale->warehouse,
                    (float) $item->quantity,
                    $sale->user,
                    "Reversión por cancelación de venta {$sale->reference}",
                    $sale,
                );
            }



            $sale->update([
                'status' => SaleStatus::CANCELLED,
            ]);

            return $sale->fresh([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
                'inventoryMovements',
            ]);
        });
    }

    /**
     * Valida que existan productos y que el stock sea suficiente.
     */
    private function validateItems(array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'La venta debe tener al menos un producto.',
            ]);
        }

        foreach ($items as $item) {
            if (
                ! isset($item['product_id']) ||
                ! isset($item['tax_id']) ||
                ! isset($item['quantity']) ||
                ! isset($item['unit_price']) ||
                ! isset($item['tax_amount']) ||
                ! isset($item['subtotal']) ||
                ! isset($item['total'])
            ) {
                throw ValidationException::withMessages([
                    'items' => 'Cada producto debe incluir product_id, tax_id, quantity, unit_price, tax_amount, subtotal y total.',
                ]);
            }
        }
    }


    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {

            if ($sale->status !== SaleStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Solo se pueden actualizar ventas en estado borrador.',
                ]);
            }

            $items = $data['items'] ?? [];

            $this->validateItems($items);

            $subtotal = 0;
            $tax = 0;

            foreach ($items as $item) {
                $subtotal += (float) $item['subtotal'];
                $tax += (float) $item['tax_amount'];
            }

            $sale->update([
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'reference' => $data['reference'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->saleItems()->delete();

            foreach ($items as $item) {
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'tax_id' => $item['tax_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $item['subtotal'],
                    'total' => $item['total'],
                ]);
            }

            return $sale->fresh([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
                'inventoryMovements',
            ]);
        });
    }
}
