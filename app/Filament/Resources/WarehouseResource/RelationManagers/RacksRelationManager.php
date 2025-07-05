<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\RackLevel;
use App\Models\RackBlock;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RacksRelationManager extends RelationManager
{
    protected static string $relationship = 'racks';
    protected static ?string $title = 'Rak';
    protected static ?string $recordTitleAttribute = 'code';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('rack_block_id')
                ->label('Blok')
                ->relationship('block', 'name')
                ->options(fn() => \App\Models\RackBlock::orderBy('name')->take(5)->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('rack_level_id')
                ->label('Level')
                ->relationship('level', 'name')
                ->options(fn() => \App\Models\RackLevel::orderBy('name')->take(5)->pluck('name', 'id'))
                ->searchable()
                ->native(false)
                ->required(),

            TextInput::make('code')
                ->label('Kode Rak')
                ->required(),

            TextInput::make('capacity_kg')
                ->label('Kapasitas (kg)')
                ->numeric(),

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
        return $table->columns([
            TextColumn::make('code')->sortable()->searchable(),
            TextColumn::make('block.name')->label('Blok'),
            TextColumn::make('level.name')->label('Level'),
            TextColumn::make('capacity_kg')->label('Kapasitas (kg)'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime(),
        ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ActionsEditAction::make(),
                DeleteAction::make(),
                EditAction::make()
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
