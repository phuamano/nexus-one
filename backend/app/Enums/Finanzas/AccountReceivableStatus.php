<?php

declare(strict_types=1);

namespace App\Enums\Finanzas;

enum AccountReceivableStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}
