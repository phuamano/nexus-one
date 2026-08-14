<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Suppliers;

use App\Models\Compras\Supplier;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'proveedor';

    protected static ?string $pluralModelLabel = 'proveedores';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nombre comercial')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('legal_name')
                    ->label('Razón social')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('tax_id')
                    ->label('RUC / Identificación fiscal')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(50)
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Proveedor activo')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(1),

                Textarea::make('address')
                    ->label('Dirección')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre comercial')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tax_id')
                    ->label('RUC / ID fiscal')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('purchases_count')
                    ->label('Compras')
                    ->counts('purchases')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('purchases');
    }
}
