<?php

namespace App\Filament\Resources\Finanzas\FinancialMovements\Pages;

use App\Filament\Resources\Finanzas\FinancialMovements\FinancialMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialMovement extends EditRecord
{
    protected static string $resource = FinancialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
