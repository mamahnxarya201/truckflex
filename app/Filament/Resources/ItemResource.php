<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(255),

                Select::make('item_type_id')
                    ->label('Tipe Barang')
                    ->relationship('itemType', 'name')
                    ->required()
                    ->searchable(),

                TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('pcs, kg, box, dst'),

                TextInput::make('weight_kg')
                    ->label('Berat (kg)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->step('0.001'),

                TextInput::make('cost_price')
                    ->label('Harga Modal')
                    ->numeric()
                    ->inputMode('decimal')
                    ->prefix('Rp')
                    ->step('0.01'),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('itemType.name')
                    ->label('Tipe')
                    ->sortable()
                    ->badge(),

                TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),

                TextColumn::make('weight_kg')
                    ->label('Berat (kg)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('cost_price')
                    ->label('Harga Modal')
                    ->money('IDR') // Bisa ganti sesuai currency
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true, true),
            ])
            ->filters([
                SelectFilter::make('item_type_id')
                    ->label('Filter Tipe Barang')
                    ->relationship('itemType', 'name'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
