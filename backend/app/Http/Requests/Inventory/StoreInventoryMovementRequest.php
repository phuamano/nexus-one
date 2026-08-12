<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid'],
            'warehouse_id' => ['required', 'uuid'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
            'direction' => ['nullable', 'in:in,out'],
        ];
    }
}
