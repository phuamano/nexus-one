<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountPayables;

use App\Filament\Resources\Finanzas\AccountPayables\Pages\CreateAccountPayable;
use App\Filament\Resources\Finanzas\AccountPayables\Pages\EditAccountPayable;
use App\Filament\Resources\Finanzas\AccountPayables\Pages\ListAccountPayables;
use App\Filament\Resources\Finanzas\AccountPayables\Schemas\AccountPayableForm;
use App\Filament\Resources\Finanzas\AccountPayables\Tables\AccountPayablesTable;
use App\Models\Finanzas\AccountPayable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountPayableResource extends Resource
{
    protected static ?string $model = AccountPayable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Cuentas por pagar';

    protected static ?string $modelLabel = 'cuenta por pagar';

    protected static ?string $pluralModelLabel = 'cuentas por pagar';

    public static function form(Schema $schema): Schema
    {
        return AccountPayableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountPayablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountPayables::route('/'),
            'create' => CreateAccountPayable::route('/create'),
            'edit' => EditAccountPayable::route('/{record}/edit'),
        ];
    }
}
