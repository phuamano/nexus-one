<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountPayables\Tables;

use App\Enums\Finanzas\AccountPayableStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase.reference')
                    ->label('Compra')
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
                    ->label('Pagado')
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
                        fn (AccountPayableStatus $state): string => match ($state) {
                            AccountPayableStatus::PENDING => 'Pendiente',
                            AccountPayableStatus::PARTIAL => 'Parcial',
                            AccountPayableStatus::PAID => 'Pagada',
                            AccountPayableStatus::CANCELLED => 'Cancelada',
                        }
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(AccountPayableStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
