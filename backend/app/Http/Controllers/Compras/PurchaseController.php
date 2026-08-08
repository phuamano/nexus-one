<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compras\StorePurchaseRequest;
use App\Http\Requests\Compras\UpdatePurchaseRequest;
use App\Models\Compras\Purchase;
use App\Services\Compras\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchaseService,
    ) {
    }

    public function index(): JsonResponse
    {
        $purchases = Purchase::query()
            ->with([
                'supplier',
                'warehouse',
                'user',
                'purchaseItems.product',
                'purchaseItems.tax',
            ])
            ->latest('purchase_date')
            ->paginate(15);

        return response()->json([
            'data' => $purchases,
        ]);
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        $purchase = $this->purchaseService->create($data);

        return response()->json([
            'message' => 'Compra creada correctamente.',
            'data' => $purchase,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $purchase = Purchase::query()
            ->with([
                'supplier',
                'warehouse',
                'user',
                'purchaseItems.product',
                'purchaseItems.tax',
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => $purchase,
        ]);
    }

    public function update(
        UpdatePurchaseRequest $request,
        string $id
    ): JsonResponse {
        $purchase = Purchase::query()->findOrFail($id);

        $purchase = $this->purchaseService->update(
            $purchase,
            $request->validated()
        );

        return response()->json([
            'message' => 'Compra actualizada correctamente.',
            'data' => $purchase,
        ]);
    }

    public function confirm(string $id): JsonResponse
    {
        $purchase = Purchase::query()->findOrFail($id);

        $purchase = $this->purchaseService->confirm($purchase);

        return response()->json([
            'message' => 'Compra confirmada correctamente.',
            'data' => $purchase,
        ]);
    }
}
