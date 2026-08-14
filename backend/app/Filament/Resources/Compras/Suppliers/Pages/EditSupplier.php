<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Suppliers\Pages;

use App\Filament\Resources\Compras\Suppliers\SupplierResource;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
