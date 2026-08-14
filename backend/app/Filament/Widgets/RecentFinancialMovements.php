<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Finanzas\FinancialMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentFinancialMovements extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Movimientos financieros recientes')
            ->query(
                fn (): Builder => FinancialMovement::query()
                    ->with('account')
                    ->latest('movement_date')
                    ->latest('created_at')
            )
            ->paginated(false)
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(
                        fn (FinancialMovementType $state): string => match ($state) {
                            FinancialMovementType::INCOME => 'Ingreso',
                            FinancialMovementType::EXPENSE => 'Gasto',
                            FinancialMovementType::TRANSFER => 'Transferencia',
                            FinancialMovementType::ADJUSTMENT => 'Ajuste',
                        }
                    ),

                TextColumn::make('direction')
                    ->label('Movimiento')
                    ->badge()
                    ->formatStateUsing(
                        fn (FinancialMovementDirection $state): string => match ($state) {
                            FinancialMovementDirection::IN => 'Entrada',
                            FinancialMovementDirection::OUT => 'Salida',
                        }
                    ),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
