<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountReceivables\Schemas;

use App\Enums\Finanzas\AccountReceivableStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->disabled(),

                Select::make('sale_id')
                    ->label('Venta')
                    ->relationship('sale', 'reference')
                    ->disabled(),

                DatePicker::make('issue_date')
                    ->label('Fecha de emisión')
                    ->disabled(),

                DatePicker::make('due_date')
                    ->label('Fecha de vencimiento')
                    ->disabled(),

                TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->prefix('S/')
                    ->disabled(),

                TextInput::make('paid_amount')
                    ->label('Cobrado')
                    ->numeric()
                    ->prefix('S/')
                    ->disabled(),

                TextInput::make('balance')
                    ->label('Saldo pendiente')
                    ->numeric()
                    ->prefix('S/')
                    ->disabled(),

                Select::make('status')
                    ->label('Estado')
                    ->options(AccountReceivableStatus::class)
                    ->disabled(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
