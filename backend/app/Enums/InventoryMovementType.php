<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryMovementType: string
{
    case Purchase = 'purchase';

    case Sale = 'sale';

    case Adjustment = 'adjustment';

    case Transfer = 'transfer';

    case Return = 'return';
}
