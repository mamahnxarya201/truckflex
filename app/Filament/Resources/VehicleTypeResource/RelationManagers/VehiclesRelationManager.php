<?php

namespace App\Filament\Resources\VehicleTypeResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';
    protected static ?string $title = 'Kendaraan';
    protected static ?string $recordTitleAttribute = 'license_plate';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('license_plate')
                ->label('Nomor Plat')
                ->required(),

            TextInput::make('chassis_number')
                ->label('Nomor Rangka')
                ->required(),

            TextInput::make('engine_number')
                ->label('Nomor Mesin')
                ->required(),

            TextInput::make('year')
                ->label('Tahun')
                ->numeric()
                ->required(),

            TextInput::make('color')
                ->label('Warna'),

            TextInput::make('current_km')
                ->label('Kilometer Saat Ini')
                ->numeric(),

            DatePicker::make('last_maintenance_at')
                ->label('Terakhir Maintenance'),

            Toggle::make('is_available')
                ->label('Tersedia')
                ->default(true),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),

            Textarea::make('note')
                ->label('Catatan')
                ->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_plate')
                    ->label('Nomor Plat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chassis_number')
                    ->label('Nomor Rangka')
                    ->searchable(),
                TextColumn::make('engine_number')
                    ->label('Nomor Mesin')
                    ->searchable(),
                TextColumn::make('year')
                    ->label('Tahun'),
                TextColumn::make('color')
                    ->label('Warna'),
                TextColumn::make('current_km')
                    ->label('Kilometer'),
                TextColumn::make('last_maintenance_at')
                    ->label('Terakhir Maintenance')
                    ->date(),
                IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
