<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemTypeResource\Pages;
use App\Filament\Resources\ItemTypeResource\RelationManagers;
use App\Models\ItemType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ItemTypeResource extends Resource
{
    protected static ?string $model = ItemType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Tipe Barang';
    protected static ?string $pluralModelLabel = 'Tipe Barang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->label('Kode')
                ->unique(ignoreRecord: true)
                ->required()
                ->maxLength(50),

            TextInput::make('name')
                ->label('Nama Tipe')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->maxLength(1000),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Tipe')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemTypes::route('/'),
            'create' => Pages\CreateItemType::route('/create'),
            'edit' => Pages\EditItemType::route('/{record}/edit'),
        ];
    }
}
