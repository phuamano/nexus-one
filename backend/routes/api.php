<?php

declare(strict_types=1);

use App\Http\Controllers\Compras\PurchaseController;
use App\Http\Controllers\Finanzas\PayablePaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
    Route::put('/purchases/{id}', [PurchaseController::class, 'update']);
    Route::post('/purchases/{id}/confirm', [PurchaseController::class, 'confirm']);

    Route::post(
        'account-payables/{id}/payments',
        [PayablePaymentController::class, 'store']
    );
});
