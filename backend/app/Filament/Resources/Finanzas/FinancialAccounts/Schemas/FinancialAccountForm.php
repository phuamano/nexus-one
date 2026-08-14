<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\FinancialAccounts\Schemas;

use App\Enums\Finanzas\FinancialAccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FinancialAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Tipo')
                    ->options(FinancialAccountType::class)
                    ->required(),

                TextInput::make('currency')
                    ->label('Moneda')
                    ->required()
                    ->default('PEN')
                    ->length(3)
                    ->maxLength(3),

                TextInput::make('initial_balance')
                    ->label('Saldo inicial')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('S/'),

                TextInput::make('current_balance')
                    ->label('Saldo actual')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('S/'),

                Toggle::make('is_active')
                    ->label('Cuenta activa')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
