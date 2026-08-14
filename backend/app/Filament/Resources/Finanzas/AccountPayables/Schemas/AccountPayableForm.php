<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountPayables\Schemas;

use App\Enums\Finanzas\AccountPayableStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->disabled(),

                Select::make('purchase_id')
                    ->label('Compra')
                    ->relationship('purchase', 'reference')
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
                    ->label('Pagado')
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
                    ->options(AccountPayableStatus::class)
                    ->disabled(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
