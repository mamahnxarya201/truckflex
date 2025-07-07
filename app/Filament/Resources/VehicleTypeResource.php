<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleTypeResource\Pages;
use App\Filament\Resources\VehicleTypeResource\RelationManagers\VehiclesRelationManager;
use App\Models\VehicleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleTypeResource extends Resource
{
    protected static ?string $model = VehicleType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data Armada';
    protected static ?string $modelLabel = 'Tipe Kendaraan';
    protected static ?string $pluralModelLabel = 'Tipe Kendaraan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                Forms\Components\TextInput::make('brand')
                    ->label('Merek')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
                Forms\Components\TextInput::make('max_weight_kg')
                    ->label('Berat Maksimal (kg)')
                    ->numeric(),
                Forms\Components\TextInput::make('truck_weight_kg')
                    ->label('Berat Kendaraan (kg)')
                    ->numeric(),
                Forms\Components\TextInput::make('fuel_capacity')
                    ->label('Kapasitas Bahan Bakar (L)')
                    ->numeric(),
                Forms\Components\TextInput::make('fuel_consumption')
                    ->label('Konsumsi Bahan Bakar (km/L)')
                    ->numeric(),
                Forms\Components\TextInput::make('license_type_required')
                    ->label('Tipe SIM')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_weight_kg')
                    ->label('Berat Maksimal (kg)'),
                Tables\Columns\TextColumn::make('truck_weight_kg')
                    ->label('Berat Kendaraan (kg)'),
                Tables\Columns\TextColumn::make('fuel_capacity')
                    ->label('Kapasitas Bahan Bakar (L)'),
                Tables\Columns\TextColumn::make('fuel_consumption')
                    ->label('Konsumsi Bahan Bakar (km/L)'),
                Tables\Columns\TextColumn::make('license_type_required')
                    ->label('Tipe SIM')
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
            VehiclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleTypes::route('/'),
            'create' => Pages\CreateVehicleType::route('/create'),
            'edit' => Pages\EditVehicleType::route('/{record}/edit'),
        ];
    }
}
