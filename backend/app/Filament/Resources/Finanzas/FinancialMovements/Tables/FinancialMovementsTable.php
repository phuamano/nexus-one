<?php

namespace App\Filament\Resources\Finanzas\FinancialMovements\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

                TextColumn::make('direction')
                    ->label('Dirección')
                    ->badge()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
