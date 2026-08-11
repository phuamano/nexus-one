<?php

declare(strict_types=1);

namespace App\Models\Finanzas;

use App\Models\TenantModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'account_payable_id',
    'user_id',
    'payment_date',
    'amount',
    'method',
    'reference',
    'notes',
])]
class PayablePayment extends TenantModel
{
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(
            AccountPayable::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
