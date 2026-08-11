<?php

declare(strict_types=1);

namespace App\Models\Finanzas;

use App\Enums\Finanzas\AccountReceivableStatus;
use App\Models\TenantModel;
use App\Models\User;
use App\Models\Ventas\Customer;
use App\Models\Ventas\Sale;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'customer_id',
    'sale_id',
    'issue_date',
    'due_date',
    'amount',
    'paid_amount',
    'balance',
    'status',
    'notes',
])]
class AccountReceivable extends TenantModel
{
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'status' => AccountReceivableStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
