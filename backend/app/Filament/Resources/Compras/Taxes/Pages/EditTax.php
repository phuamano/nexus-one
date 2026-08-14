<?php

namespace App\Filament\Resources\Compras\Taxes\Pages;

use App\Filament\Resources\Compras\Taxes\TaxResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTax extends EditRecord
{
    protected static string $resource = TaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
