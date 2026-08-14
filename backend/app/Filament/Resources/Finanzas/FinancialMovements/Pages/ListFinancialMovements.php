<?php

namespace App\Filament\Resources\Finanzas\FinancialMovements\Pages;

use App\Filament\Resources\Finanzas\FinancialMovements\FinancialMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialMovements extends ListRecords
{
    protected static string $resource = FinancialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
