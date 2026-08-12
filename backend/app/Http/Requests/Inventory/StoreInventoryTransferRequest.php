<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid'],
            'from_warehouse_id' => ['required', 'uuid'],
            'to_warehouse_id' => ['required', 'uuid', 'different:from_warehouse_id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
