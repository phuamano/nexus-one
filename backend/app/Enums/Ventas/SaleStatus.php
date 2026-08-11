<?php

declare(strict_types=1);

namespace App\Enums\Ventas;

enum SaleStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
}
