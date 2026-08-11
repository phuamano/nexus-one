<?php

declare(strict_types=1);

namespace App\Models\Ventas;

use App\Enums\Ventas\SaleStatus;
use App\Models\TenantModel;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'company_id',
    'customer_id',
    'warehouse_id',
    'user_id',
    'sale_date',
    'reference',
    'status',
    'subtotal',
    'tax',
    'total',
    'notes',
])]
class Sale extends TenantModel
{
    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'status' => SaleStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(
            \App\Models\InventoryMovement::class,
            'reference'
        );
    }
}
