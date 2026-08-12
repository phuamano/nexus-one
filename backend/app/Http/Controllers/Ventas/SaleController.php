<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\StoreSaleRequest;
use App\Http\Requests\Ventas\UpdateSaleRequest;
use App\Models\Ventas\Sale;
use App\Services\Ventas\SaleService;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {
    }

    public function index(): JsonResponse
    {
        $sales = Sale::query()
            ->with([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
            ])
            ->latest('sale_date')
            ->paginate(15);

        return response()->json([
            'data' => $sales,
        ]);
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        $sale = $this->saleService->create($data);

        return response()->json([
            'message' => 'Venta creada correctamente.',
            'data' => $sale,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $sale = Sale::query()
            ->with([
                'customer',
                'warehouse',
                'user',
                'saleItems.product',
                'saleItems.tax',
                'inventoryMovements',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => $sale,
        ]);
    }

    public function update(
        UpdateSaleRequest $request,
        string $id
    ): JsonResponse {
        $sale = Sale::query()->findOrFail($id);

        $sale = $this->saleService->update(
            $sale,
            $request->validated()
        );

        return response()->json([
            'message' => 'Venta actualizada correctamente.',
            'data' => $sale,
        ]);
    }

    public function confirm(string $id): JsonResponse
    {
        $sale = Sale::query()->findOrFail($id);

        $sale = $this->saleService->confirm($sale);

        return response()->json([
            'message' => 'Venta confirmada correctamente.',
            'data' => $sale,
        ]);
    }

    public function cancel(string $id): JsonResponse
    {
        $sale = Sale::query()->findOrFail($id);

        $sale = $this->saleService->cancel($sale);

        return response()->json([
            'message' => 'Venta cancelada correctamente.',
            'data' => $sale,
        ]);
    }
}
