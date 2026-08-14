<?php

namespace App\Filament\Resources\Finanzas\AccountReceivables\Pages;

use App\Filament\Resources\Finanzas\AccountReceivables\AccountReceivableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountReceivable extends EditRecord
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
