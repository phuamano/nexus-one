<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Suppliers\Pages;

use App\Filament\Resources\Compras\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
}
