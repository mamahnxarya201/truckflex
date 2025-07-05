<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Filament\Resources\WarehouseResource\RelationManagers\RacksRelationManager;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data Penyimpanan';
    protected static ?string $modelLabel = 'Gudang';
    protected static ?string $pluralModelLabel = 'Gudang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')->required()->unique(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Textarea::make('description')->rows(3),
                Forms\Components\TextInput::make('address')->columnSpanFull(),
                Forms\Components\Select::make('manager_id')
                    ->label('Penanggung Jawab')
                    ->relationship('manager', 'name')
                    ->options(fn() => \App\Models\User::orderBy('name')->take(5)->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('zone')->label('Zona')->nullable(),
                Forms\Components\TextInput::make('type')->label('Tipe')->nullable(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('manager.name')->label('Penanggung Jawab'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('zone'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            RacksRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
