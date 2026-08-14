<?php

namespace App\Filament\Resources\Finanzas\FinancialMovements;

use App\Filament\Resources\Finanzas\FinancialMovements\Pages\CreateFinancialMovement;
use App\Filament\Resources\Finanzas\FinancialMovements\Pages\EditFinancialMovement;
use App\Filament\Resources\Finanzas\FinancialMovements\Pages\ListFinancialMovements;
use App\Filament\Resources\Finanzas\FinancialMovements\Schemas\FinancialMovementForm;
use App\Filament\Resources\Finanzas\FinancialMovements\Tables\FinancialMovementsTable;
use App\Models\Finanzas\FinancialMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialMovementResource extends Resource
{
    protected static ?string $model = FinancialMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FinancialMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialMovements::route('/'),
            'create' => CreateFinancialMovement::route('/create'),
            'edit' => EditFinancialMovement::route('/{record}/edit'),
        ];
    }
}
