<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\FinancialAccounts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Moneda')
                    ->sortable(),

                TextColumn::make('initial_balance')
                    ->label('Saldo inicial')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('current_balance')
                    ->label('Saldo actual')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
