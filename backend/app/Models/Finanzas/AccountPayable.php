<?php

declare(strict_types=1);

namespace App\Models\Finanzas;

use App\Enums\Finanzas\AccountPayableStatus;
use App\Models\Compras\Purchase;
use App\Models\Compras\Supplier;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'supplier_id',
    'purchase_id',
    'issue_date',
    'due_date',
    'amount',
    'paid_amount',
    'balance',
    'status',
    'notes',
])]
class AccountPayable extends TenantModel
{
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'status' => AccountPayableStatus::class,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayablePayment::class);
    }
}
