<?php

declare(strict_types=1);

namespace App\Models\Ventas;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'legal_name',
    'tax_id',
    'email',
    'phone',
    'address',
    'is_active',
])]
class Customer extends TenantModel
{
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
