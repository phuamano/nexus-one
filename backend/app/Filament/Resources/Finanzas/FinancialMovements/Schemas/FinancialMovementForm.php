<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\FinancialMovements\Schemas;

use App\Enums\Finanzas\FinancialMovementDirection;
use App\Enums\Finanzas\FinancialMovementType;
use App\Models\Finanzas\FinancialAccount;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FinancialMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('financial_account_id')
                    ->label('Cuenta financiera')
                    ->options(
                        FinancialAccount::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Tipo de movimiento')
                    ->options([
                        FinancialMovementType::INCOME->value => 'Ingreso',
                        FinancialMovementType::EXPENSE->value => 'Egreso',
                        FinancialMovementType::ADJUSTMENT->value => 'Ajuste',
                    ])
                    ->required(),

                Select::make('direction')
                    ->label('Dirección')
                    ->options(FinancialMovementDirection::class)
                    ->required(),

                TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('S/'),

                DatePicker::make('movement_date')
                    ->label('Fecha')
                    ->required()
                    ->default(now()),

                TextInput::make('reference')
                    ->label('Referencia')
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
