<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountReceivables\Tables;

use App\Enums\Finanzas\AccountReceivableStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountReceivablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sale.reference')
                    ->label('Venta')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('issue_date')
                    ->label('Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Cobrado')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (AccountReceivableStatus $state): string => match ($state) {
                            AccountReceivableStatus::PENDING => 'Pendiente',
                            AccountReceivableStatus::PARTIAL => 'Parcial',
                            AccountReceivableStatus::PAID => 'Pagada',
                            AccountReceivableStatus::CANCELLED => 'Cancelada',
                        }
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(AccountReceivableStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
