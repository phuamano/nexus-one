<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryMovement;

class InventoryReportService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    private function stockExpression(): string
    {
        return "
        COALESCE(
            SUM(
                CASE
                    WHEN inventory_movements.direction = 'in'
                        THEN inventory_movements.quantity
                    ELSE -inventory_movements.quantity
                END
            ),
            0
        )
    ";
    }

    public function stockByWarehouse(
        Product $product,
        Warehouse $warehouse,
    ): float
    {
        return $product->stockInWarehouse($warehouse);
    }

    public function kardex(Product $product)
    {
        $balances = [];

        return InventoryMovement::query()
            ->where('product_id', $product->id)
            ->with([
                'warehouse',
                'user',
            ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function (InventoryMovement $movement) use (&$balances) {

                $warehouseId = $movement->warehouse_id;

                $balances[$warehouseId] ??= 0.0;

                $quantity = (float) $movement->quantity;

                if ($movement->direction === 'in') {
                    $balances[$warehouseId] += $quantity;
                } else {
                    $balances[$warehouseId] -= $quantity;
                }

                $movement->balance = $balances[$warehouseId];

                return $movement;
            })
            ->reverse()
            ->values();
    }

    public function lowStock()
    {
        return Product::query()
            ->leftJoin(
                'inventory_movements',
                'products.id',
                '=',
                'inventory_movements.product_id'
            )
            ->select(
                'products.id',
                'products.name',
                'products.stock_min',
            )
            ->selectRaw("{$this->stockExpression()} AS stock")
            ->groupBy(
                'products.id',
                'products.name',
                'products.stock_min',
            )
            ->havingRaw("{$this->stockExpression()} <= products.stock_min")
            ->get();
    }

}
