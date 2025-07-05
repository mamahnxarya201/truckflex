<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RackBlockResource\Pages;
use App\Filament\Resources\RackBlockResource\RelationManagers;
use App\Models\RackBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RackBlockResource extends Resource
{
    protected static ?string $model = RackBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationGroup = 'Master Data Penyimpanan';
    protected static ?string $modelLabel = 'Blok Rak';
    protected static ?string $pluralModelLabel = 'Blok Rak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')->required()->unique(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Toggle::make('forklift_accessible')->label('Forklift Accessible')->default(false),
                Forms\Components\Textarea::make('note')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('forklift_accessible')->boolean(),
                Tables\Columns\TextColumn::make('note')->limit(20)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRackBlocks::route('/'),
            'create' => Pages\CreateRackBlock::route('/create'),
            'edit' => Pages\EditRackBlock::route('/{record}/edit'),
        ];
    }
}
