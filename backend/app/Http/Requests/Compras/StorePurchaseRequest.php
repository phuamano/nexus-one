<?php

declare(strict_types=1);

namespace App\Http\Requests\Compras;

use App\Enums\Compras\PurchaseStatus;
use App\Services\CurrentCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            'supplier_id' => [
                'required',
                'uuid',
                Rule::exists('suppliers', 'id')
                    ->where('company_id', $companyId),
            ],

            'warehouse_id' => [
                'required',
                'uuid',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId),
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'status' => [
                    'required',
                    new Enum(PurchaseStatus::class),
                ],
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

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'gte:0',
            ],
        ];
    }
}
