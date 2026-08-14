<?php

namespace App\Filament\Resources\Compras\Taxes\Pages;

use App\Filament\Resources\Compras\Taxes\TaxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxes extends ListRecords
{
    protected static string $resource = TaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
