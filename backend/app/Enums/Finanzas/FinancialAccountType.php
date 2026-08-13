<?php

declare(strict_types=1);

namespace App\Enums\Finanzas;

enum FinancialAccountType: string
{
    case CASH = 'cash';
    case BANK = 'bank';
}
