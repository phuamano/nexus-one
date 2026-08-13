<?php

declare(strict_types=1);

namespace App\Models\Finanzas;

use App\Enums\Finanzas\FinancialAccountType;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'code',
    'type',
    'currency',
    'initial_balance',
    'current_balance',
    'is_active',
    'notes',
])]
class FinancialAccount extends TenantModel
{
    protected function casts(): array
    {
        return [
            'type' => FinancialAccountType::class,
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(FinancialMovement::class);
    }
}
