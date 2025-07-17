<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleLogResource\Pages;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VehicleLogResource extends Resource
{
    protected static ?string $model = VehicleLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Master Data Armada';
    
    protected static ?string $modelLabel = 'Catatan Kendaraan';
    protected static ?string $pluralModelLabel = 'Catatan Kendaraan';

    /**
     * Determine if this resource should be accessible for the current user
     */
    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Only users with driver role can access this resource
        // Other roles (admin, warehouse_manager) will use the relation manager instead
        return $user && $user->hasRole('driver');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Driver can only see logs related to vehicles they've driven
        if ($user && $user->hasRole('driver')) {
            $query->where('driver_id', $user->id);
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->options(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        $vehicleQuery = Vehicle::query();
                        
                        // Driver only sees vehicles they've used or are assigned to them
                        if ($user && $user->hasRole('driver')) {
                            // Get vehicles from existing logs
                            $logVehicleIds = VehicleLog::where('driver_id', $user->id)
                                ->distinct()
                                ->pluck('vehicle_id');
                            
                            // Get vehicles from assigned deliveries
                            $deliveryVehicleIds = \App\Models\Delivery::where('driver_id', $user->id)
                                ->distinct()
                                ->pluck('vehicle_id');
                            
                            // Combine both sources
                            $allVehicleIds = $logVehicleIds->merge($deliveryVehicleIds)->unique();
                            
                            if ($allVehicleIds->count() > 0) {
                                $vehicleQuery->whereIn('id', $allVehicleIds);
                            }
                        }
                        
                        return $vehicleQuery->pluck('license_plate', 'id');
                    })
                    ->required()
                    ->searchable(),
                    
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\DateTimePicker::make('log_time')
                    ->label('Waktu Kejadian')
                    ->required()
                    ->default(now()),
                    
                Forms\Components\Select::make('driver_id')
                    ->label('Pengemudi')
                    ->options(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        if ($user && $user->hasRole('driver')) {
                            // Driver can only select themselves
                            return [$user->id => $user->name];
                        }
                        
                        return User::role('driver')->pluck('name', 'id');
                    })
                    ->default(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        if ($user && $user->hasRole('driver')) {
                            return $user->id;
                        }
                        
                        return null;
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('log_type')
                    ->label('Tipe Catatan')
                    ->required()
                    ->options([
                        'trip' => 'Perjalanan',
                        'fuel' => 'Pengisian BBM',
                        'maintenance' => 'Maintenance',
                        'incident' => 'Insiden',
                    ]),
                    
                Forms\Components\Select::make('delivery_id')
                    ->label('Pengiriman')
                    ->options(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        $deliveryQuery = Delivery::query();
                        
                        // Driver only sees their assigned deliveries
                        if ($user && $user->hasRole('driver')) {
                            $deliveryQuery->where('driver_id', $user->id);
                        }
                        
                        return $deliveryQuery->pluck('id', 'id');
                    })
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Toggle::make('is_resolved')
                    ->label('Sudah Selesai')
                    ->default(true)
                    ->helperText('Tandai apakah permasalahan sudah ditangani'),
                    
                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->required()
                    ->rows(3)
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Nomor Polisi')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('log_time')
                    ->label('Waktu Kejadian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Pengemudi')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('log_type')
                    ->label('Tipe Catatan')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'trip' => 'Perjalanan',
                        'fuel' => 'Pengisian BBM',
                        'maintenance' => 'Maintenance',
                        'incident' => 'Insiden',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('delivery_id')
                    ->label('Pengiriman ID')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30),
                    
                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('Selesai')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver_id')
                    ->label('Pengemudi')
                    ->relationship('driver', 'name'),
                    
                Tables\Filters\SelectFilter::make('log_type')
                    ->label('Tipe Catatan')
                    ->options([
                        'trip' => 'Perjalanan',
                        'fuel' => 'Pengisian BBM',
                        'maintenance' => 'Maintenance',
                        'incident' => 'Insiden',
                    ]),
                    
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'license_plate'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(function ($record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    
                    // Drivers can only edit logs they created
                    return !$user->hasRole('driver') || $user->id === $record->driver_id;
                }),
                Tables\Actions\DeleteAction::make()->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    
                    // Only admins and managers can delete
                    return !$user->hasRole('driver');
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        // Only admins and managers can bulk delete
                        return !$user->hasRole('driver');
                    }),
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
            'index' => Pages\ListVehicleLogs::route('/'),
            'create' => Pages\CreateVehicleLog::route('/create'),
            'edit' => Pages\EditVehicleLog::route('/{record}/edit'),
        ];
    }    
}
