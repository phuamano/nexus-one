<?php

namespace App\Filament\Resources\Finanzas\AccountPayables\Pages;

use App\Filament\Resources\Finanzas\AccountPayables\AccountPayableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountPayable extends EditRecord
{
    protected static string $resource = AccountPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
