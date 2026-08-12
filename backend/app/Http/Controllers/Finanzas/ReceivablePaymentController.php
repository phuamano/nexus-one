<?php

declare(strict_types=1);

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Finanzas\AccountReceivable;
use App\Services\Finanzas\ReceivablePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivablePaymentController extends Controller
{
    public function __construct(
        private readonly ReceivablePaymentService $paymentService,
    ) {
    }

    public function store(
        Request $request,
        string $id
    ): JsonResponse {
        $account = AccountReceivable::query()->findOrFail($id);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $this->paymentService->pay(
            $account,
            $data['amount'],
            $data['payment_method'],
            $data['reference'] ?? null,
            $data['notes'] ?? null,
            $request->user(),
        );

        return response()->json([
            'message' => 'Pago registrado correctamente.',
            'data' => $payment,
        ], 201);
    }
}
