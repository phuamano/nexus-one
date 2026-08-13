<?php

declare(strict_types=1);

namespace App\Models\Finanzas;

use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\TenantModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'company_id',
    'financial_account_id',
    'user_id',
    'type',
    'direction',
    'reference_type',
    'reference_id',
    'amount',
    'movement_date',
    'reference',
    'notes',
])]
class FinancialMovement extends TenantModel
{
    protected function casts(): array
    {
        return [
            'type' => FinancialMovementType::class,
            'direction' => FinancialMovementDirection::class,
            'amount' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            FinancialAccount::class,
            'financial_account_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
