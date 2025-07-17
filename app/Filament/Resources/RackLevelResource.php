<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RackLevelResource\Pages;
use App\Filament\Resources\RackLevelResource\RelationManagers;
use App\Models\RackLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RackLevelResource extends Resource
{
    protected static ?string $model = RackLevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Master Data Penyimpanan';
    protected static ?string $modelLabel = 'Level Rak';
    protected static ?string $pluralModelLabel = 'Level Rak';
    
    /**
     * Control access to this resource
     */
    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && $user->hasPermissionTo('view-rack-levels');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('height_cm')->numeric()->required(),
                Forms\Components\TextInput::make('max_load_kg')->numeric(),
                Forms\Components\Textarea::make('note')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('height_cm')->label('Height (cm)'),
                Tables\Columns\TextColumn::make('max_load_kg')->label('Max Load (kg)'),
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
            'index' => Pages\ListRackLevels::route('/'),
            'create' => Pages\CreateRackLevel::route('/create'),
            'edit' => Pages\EditRackLevel::route('/{record}/edit'),
        ];
    }
}
