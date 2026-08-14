<?php

namespace App\Filament\Resources\Compras\Taxes;

use App\Filament\Resources\Compras\Taxes\Pages\CreateTax;
use App\Filament\Resources\Compras\Taxes\Pages\EditTax;
use App\Filament\Resources\Compras\Taxes\Pages\ListTaxes;
use App\Filament\Resources\Compras\Taxes\Schemas\TaxForm;
use App\Filament\Resources\Compras\Taxes\Tables\TaxesTable;
use App\Models\Compras\Tax;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TaxResource extends Resource
{
    protected static ?string $model = Tax::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Tax';

    public static function form(Schema $schema): Schema
    {
        return TaxForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxesTable::configure($table);
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
            'index' => ListTaxes::route('/'),
            'create' => CreateTax::route('/create'),
            'edit' => EditTax::route('/{record}/edit'),
        ];
    }
}
