<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finanzas\AccountReceivables;

use App\Filament\Resources\Finanzas\AccountReceivables\Pages\CreateAccountReceivable;
use App\Filament\Resources\Finanzas\AccountReceivables\Pages\EditAccountReceivable;
use App\Filament\Resources\Finanzas\AccountReceivables\Pages\ListAccountReceivables;
use App\Filament\Resources\Finanzas\AccountReceivables\Schemas\AccountReceivableForm;
use App\Filament\Resources\Finanzas\AccountReceivables\Tables\AccountReceivablesTable;
use App\Models\Finanzas\AccountReceivable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountReceivableResource extends Resource
{
    protected static ?string $model = AccountReceivable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Cuentas por cobrar';

    protected static ?string $modelLabel = 'cuenta por cobrar';

    protected static ?string $pluralModelLabel = 'cuentas por cobrar';

    public static function form(Schema $schema): Schema
    {
        return AccountReceivableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountReceivablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountReceivables::route('/'),
            'create' => CreateAccountReceivable::route('/create'),
            'edit' => EditAccountReceivable::route('/{record}/edit'),
        ];
    }
}
