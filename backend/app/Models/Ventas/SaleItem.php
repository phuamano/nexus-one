<?php

declare(strict_types=1);

namespace App\Models\Ventas;

use App\Models\Product;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'sale_id',
    'product_id',
    'tax_id',
    'quantity',
    'unit_price',
    'tax_amount',
    'subtotal',
    'total',
])]
class SaleItem extends TenantModel
{
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Compras\Tax::class
        );
    }
}
