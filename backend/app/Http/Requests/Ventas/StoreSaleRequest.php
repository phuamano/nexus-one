<?php

declare(strict_types=1);

namespace App\Http\Requests\Ventas;

use App\Enums\Ventas\SaleStatus;
use App\Services\CurrentCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            'customer_id' => [
                'required',
                'uuid',
                Rule::exists('customers', 'id')
                    ->where('company_id', $companyId),
            ],

            'warehouse_id' => [
                'required',
                'uuid',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId),
            ],

            'sale_date' => [
                'required',
                'date',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                new Enum(SaleStatus::class),
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'uuid',
                Rule::exists('products', 'id')
                    ->where('company_id', $companyId),
            ],

            'items.*.tax_id' => [
                'required',
                'uuid',
                Rule::exists('taxes', 'id')
                    ->where('company_id', $companyId),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'items.*.tax_amount' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'items.*.subtotal' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'items.*.total' => [
                'required',
                'numeric',
                'gte:0',
            ],
        ];
    }
}
