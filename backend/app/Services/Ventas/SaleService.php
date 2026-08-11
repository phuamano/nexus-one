<?php
declare(strict_types=1);

namespace App\Services\Ventas;

use App\Enums\Ventas\SaleStatus;
use App\Models\InventoryMovement;
use App\Models\Ventas\Sale;
use App\Models\Ventas\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
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
            ]);

            $this->validateStock($sale);

            $this->createInventoryMovements($sale);

            $sale->update([
                'status' => SaleStatus::CONFIRMED,
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
                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'type' => 'sale_return',
                    'direction' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'notes' => "Reversión por cancelación de venta {$sale->reference}",
                    'user_id' => $sale->user_id,
                ]);
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
                ! isset($item['product']) ||
                ! isset($item['warehouse'])
            ) {
                throw ValidationException::withMessages([
                    'items' => 'Cada producto debe incluir su instancia de producto y almacén.',
                ]);
            }
        }
    }

    /**
     * Valida el stock disponible antes de confirmar.
     */
    private function validateStock(Sale $sale): void
    {
        $quantities = [];

        foreach ($sale->saleItems as $item) {
            $quantities[$item->product_id] =
                ($quantities[$item->product_id] ?? 0)
                + (float) $item->quantity;
        }

        foreach ($quantities as $productId => $quantity) {

            $item = $sale->saleItems
                ->firstWhere('product_id', $productId);

            $product = $item->product;

            $available = $product->stockInWarehouse(
                $sale->warehouse
            );

            if ($available < $quantity) {
                throw ValidationException::withMessages([
                    'items' => sprintf(
                        'Stock insuficiente para %s. Disponible: %s, solicitado: %s.',
                        $product->name,
                        $available,
                        $quantity
                    ),
                ]);
            }
        }
    }

    /**
     * Genera los movimientos de salida por la venta.
     */
    private function createInventoryMovements(Sale $sale): void
    {
        foreach ($sale->saleItems as $item) {
            InventoryMovement::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $sale->warehouse_id,
                'type' => 'sale',
                'direction' => 'out',
                'quantity' => $item->quantity,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'notes' => "Salida por venta {$sale->reference}",
                'user_id' => $sale->user_id,
            ]);
        }
    }
}
