<?php

declare(strict_types=1);

namespace App\Enums\Finanzas;

enum FinancialMovementDirection: string
{
    case IN = 'in';
    case OUT = 'out';
}
