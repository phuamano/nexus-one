<?php

namespace App\Filament\Resources\Finanzas\AccountReceivables\Pages;

use App\Filament\Resources\Finanzas\AccountReceivables\AccountReceivableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountReceivables extends ListRecords
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
