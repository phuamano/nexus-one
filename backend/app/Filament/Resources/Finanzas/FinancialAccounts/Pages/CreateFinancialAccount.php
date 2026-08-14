<?php
declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\FinancialAccounts\Pages;

use App\Filament\Resources\Finanzas\FinancialAccounts\FinancialAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialAccount extends CreateRecord
{
    protected static string $resource = FinancialAccountResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['current_balance'] = $data['initial_balance'];

        return $data;
    }
}
