<?php

declare(strict_types=1);

namespace App\Enums\Compras;

enum PurchaseStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
}
