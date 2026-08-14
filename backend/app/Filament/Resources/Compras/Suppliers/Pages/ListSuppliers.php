<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Suppliers\Pages;

use App\Filament\Resources\Compras\Suppliers\SupplierResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
