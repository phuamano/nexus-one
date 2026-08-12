<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Enums\InventoryMovementDirection;
use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryMovementRequest;
use App\Http\Requests\Inventory\StoreInventoryTransferRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryReportService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly InventoryReportService $reportService,
    ) {
    }

    public function entry(
        StoreInventoryMovementRequest $request
    ): JsonResponse {
        $product = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $this->inventoryService->entry(
            $product,
            $warehouse,
            (float) $request->quantity,
            $request->user(),
            $request->notes,
        );

        return response()->json([
            'message' => 'Entrada de inventario registrada correctamente.',
        ], 201);
    }

    public function exit(
        StoreInventoryMovementRequest $request
    ): JsonResponse {
        $product = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $this->inventoryService->exit(
            $product,
            $warehouse,
            (float) $request->quantity,
            $request->user(),
            $request->notes,
        );

        return response()->json([
            'message' => 'Salida de inventario registrada correctamente.',
        ], 201);
    }

    public function adjustment(
        StoreInventoryMovementRequest $request
    ): JsonResponse {
        $product = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $direction = $request->input('direction');

        if (! in_array($direction, ['in', 'out'], true)) {
            return response()->json([
                'message' => 'La dirección debe ser in u out.',
            ], 422);
        }

        $this->inventoryService->adjust(
            $product,
            $warehouse,
            (float) $request->quantity,
            \App\Enums\InventoryMovementDirection::from($direction),
            $request->user(),
            $request->notes,
        );

        return response()->json([
            'message' => 'Ajuste de inventario registrado correctamente.',
        ], 201);
    }

    public function transfer(
        StoreInventoryTransferRequest $request
    ): JsonResponse {
        $product = Product::findOrFail($request->product_id);
        $fromWarehouse = Warehouse::findOrFail(
            $request->from_warehouse_id
        );
        $toWarehouse = Warehouse::findOrFail(
            $request->to_warehouse_id
        );

        $this->inventoryService->transfer(
            $product,
            $fromWarehouse,
            $toWarehouse,
            (float) $request->quantity,
            $request->user(),
            $request->notes,
        );

        return response()->json([
            'message' => 'Transferencia de inventario registrada correctamente.',
        ], 201);
    }

    public function stock(
        string $product,
        string $warehouse
    ): JsonResponse {
        $productModel = Product::findOrFail($product);
        $warehouseModel = Warehouse::findOrFail($warehouse);

        return response()->json([
            'data' => [
                'product_id' => $productModel->id,
                'warehouse_id' => $warehouseModel->id,
                'stock' => $this->reportService->stockByWarehouse(
                    $productModel,
                    $warehouseModel
                ),
            ],
        ]);
    }

    public function kardex(string $product): JsonResponse
    {
        $productModel = Product::findOrFail($product);

        return response()->json([
            'data' => $this->reportService->kardex($productModel),
        ]);
    }

    public function lowStock(): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->lowStock(),
        ]);
    }
}
