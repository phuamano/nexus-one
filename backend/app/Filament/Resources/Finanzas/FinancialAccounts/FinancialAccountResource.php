<?php

namespace App\Filament\Resources\Finanzas\FinancialAccounts;

use App\Filament\Resources\Finanzas\FinancialAccounts\Pages\CreateFinancialAccount;
use App\Filament\Resources\Finanzas\FinancialAccounts\Pages\EditFinancialAccount;
use App\Filament\Resources\Finanzas\FinancialAccounts\Pages\ListFinancialAccounts;
use App\Filament\Resources\Finanzas\FinancialAccounts\Schemas\FinancialAccountForm;
use App\Filament\Resources\Finanzas\FinancialAccounts\Tables\FinancialAccountsTable;
use App\Models\Finanzas\FinancialAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialAccountResource extends Resource
{
    protected static ?string $model = FinancialAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FinancialAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialAccountsTable::configure($table);
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
            'index' => ListFinancialAccounts::route('/'),
            'create' => CreateFinancialAccount::route('/create'),
            'edit' => EditFinancialAccount::route('/{record}/edit'),
        ];
    }
}
