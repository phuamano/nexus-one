<?php

declare(strict_types=1);

use App\Http\Controllers\Compras\PurchaseController;
use App\Http\Controllers\Finanzas\PayablePaymentController;
use App\Http\Controllers\Finanzas\ReceivablePaymentController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Ventas\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Compras
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
    Route::put('/purchases/{id}', [PurchaseController::class, 'update']);
    Route::post('/purchases/{id}/confirm', [PurchaseController::class, 'confirm']);

    // Finanzas
    Route::post(
        'account-payables/{id}/payments',
        [PayablePaymentController::class, 'store']
    );

    // Ventas
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales/{id}', [SaleController::class, 'show']);
    Route::put('/sales/{id}', [SaleController::class, 'update']);
    Route::post('/sales/{id}/confirm', [SaleController::class, 'confirm']);
    Route::post('/sales/{id}/cancel', [SaleController::class, 'cancel']);

    Route::post(
        'account-receivables/{id}/payments',
        [ReceivablePaymentController::class, 'store']
    );

    Route::post('/inventory/entries', [InventoryController::class, 'entry']);
    Route::post('/inventory/exits', [InventoryController::class, 'exit']);
    Route::post('/inventory/adjustments', [InventoryController::class, 'adjustment']);
    Route::post('/inventory/transfers', [InventoryController::class, 'transfer']);

    Route::get(
        '/inventory/{product}/stock/{warehouse}',
        [InventoryController::class, 'stock']
    );

    Route::get(
        '/inventory/{product}/kardex',
        [InventoryController::class, 'kardex']
    );

    Route::get(
        '/inventory/low-stock',
        [InventoryController::class, 'lowStock']
    );
});
