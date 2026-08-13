<?php

declare(strict_types=1);

namespace App\Enums\Finanzas;

enum FinancialMovementType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
}
