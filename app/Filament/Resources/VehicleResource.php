<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data Armada';
    protected static ?string $modelLabel = 'Kendaraan';
    protected static ?string $pluralModelLabel = 'Kendaraan';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    /**
     * Check whether the user can edit the model
     */
    public static function canEdit(?object $record): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && !$user->hasRole('driver') && !$user->hasRole('warehouse_manager');
    }
    
    /**
     * Check whether the user can create a new model
     */
    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && !$user->hasRole('driver') && !$user->hasRole('warehouse_manager');
    }
    
    /**
     * Check whether the user can delete the model
     */
    public static function canDelete(?object $record): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && !$user->hasRole('driver') && !$user->hasRole('warehouse_manager');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('vehicle_type_id')
                            ->label('Tipe Kendaraan')
                            ->relationship('type', 'name')
                            ->options(VehicleType::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),
                                Forms\Components\TextInput::make('brand')
                                    ->label('Merek')
                                    ->required(),
                            ]),
                            
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Nomor Plat')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('chassis_number')
                                    ->label('Nomor Rangka')
                                    ->maxLength(50),
                                    
                                Forms\Components\TextInput::make('engine_number')
                                    ->label('Nomor Mesin')
                                    ->maxLength(50),
                            ]),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Detail Kendaraan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(now()->year + 1),
                                    
                                Forms\Components\TextInput::make('color')
                                    ->label('Warna')
                                    ->maxLength(30),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('current_km')
                                    ->label('Kilometer Saat Ini')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                    
                                Forms\Components\DateTimePicker::make('last_maintenance_at')
                                    ->label('Terakhir Maintenance')
                                    ->maxDate(now())
                                    ->format('d/m/Y H:i'),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_available')
                                    ->label('Tersedia')
                                    ->default(true)
                                    ->helperText('Kendaraan tersedia untuk digunakan'),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Status aktif kendaraan'),
                            ]),
                            
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Plat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Tipe Kendaraan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.brand')
                    ->label('Merek')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('chassis_number')
                    ->label('Nomor Rangka')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('engine_number')
                    ->label('Nomor Mesin')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('color')
                    ->label('Warna')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_maintenance_at')
                    ->label('Terakhir Maintenance')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_km')
                    ->label('Kilometer')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type_id')
                    ->label('Tipe Kendaraan')
                    ->relationship('type', 'name'),
                Tables\Filters\TrashedFilter::make(),
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
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
