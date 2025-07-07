<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\LedgerFactual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationGroup = 'Inventaris';
    
    protected static ?string $navigationLabel = 'Pengiriman';
    
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = Delivery::query();
        
        // If user has a warehouse_id (indicating they are warehouse admin)
        // filter by that warehouse
        if ($user->warehouse_id) {
            $warehouseId = $user->warehouse_id;
            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                  ->orWhere('to_warehouse_id', $warehouseId);
            });
        }
        
        return $query->where('delivery_status_id', DeliveryStatus::where('code', 'in_transit')->first()?->id ?? 0)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_code')
                            ->label('Kode Pengiriman')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Otomatis jika kosong')
                            ->helperText('Kode unik untuk pengiriman ini'),
                            
                        Forms\Components\Select::make('delivery_type')
                            ->label('Tipe Pengiriman')
                            ->options([
                                'regular' => 'Reguler',
                                'urgent' => 'Urgent',
                                'return' => 'Pengembalian',
                            ])
                            ->required(),
                            
                        Forms\Components\Select::make('delivery_status_id')
                            ->label('Status')
                            ->relationship('status', 'name')
                            ->required()
                            ->preload()
                            ->afterStateUpdated(function ($state, Forms\Set $set, ?Model $record) {
                                if (!$record) return;
                                
                                // Get status codes for reference
                                $statusRecord = DeliveryStatus::find($state);
                                if (!$statusRecord) return;
                                
                                // When status changes to in_transit
                                if ($statusRecord->code === 'in_transit' && !$record->departure_date) {
                                    $set('departure_date', now());
                                    // TODO: Create negative LedgerFactual entry for source warehouse
                                }
                                
                                // When status changes to arrived
                                if ($statusRecord->code === 'arrived' && !$record->arrival_date) {
                                    $set('arrival_date', now());
                                    // TODO: Create positive LedgerFactual entry for destination warehouse
                                }
                            }),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('from_warehouse_id')
                            ->label('Dari Gudang')
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('to_warehouse_id')
                            ->label('Ke Gudang')
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->different('from_warehouse_id'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Kendaraan & Driver')
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Kendaraan')
                            ->relationship('vehicle', 'license_plate', fn (Builder $query) => 
                                $query->where('is_available', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih kendaraan yang tersedia')
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => 
                                self::validateVehicleCapacity($get, $set)
                            ),
                            
                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('validated_by')
                            ->label('Divalidasi Oleh')
                            ->relationship('validator', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Waktu')
                    ->schema([
                        Forms\Components\DateTimePicker::make('departure_date')
                            ->label('Tanggal Keberangkatan')
                            ->format('d M Y H:i')
                            ->nullable(),
                            
                        Forms\Components\DateTimePicker::make('estimated_arrival')
                            ->label('Estimasi Tiba')
                            ->format('d M Y H:i')
                            ->nullable()
                            ->after('departure_date'),
                            
                        Forms\Components\DateTimePicker::make('arrival_date')
                            ->label('Tanggal Tiba')
                            ->format('d M Y H:i')
                            ->nullable()
                            ->after('departure_date'),
                    ])->columns(3),
                
                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delivery_code')
                    ->label('Kode Pengiriman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')
                    ->label('Dari Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')
                    ->label('Ke Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Delivery $record) => match ($record->status->code ?? '') {
                        'pending' => 'gray',
                        'in_transit' => 'warning',
                        'arrived' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => 'Reguler',
                        'urgent' => 'Urgent',
                        'return' => 'Pengembalian',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'info',
                        'urgent' => 'danger',
                        'return' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Tanggal Berangkat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Tanggal Tiba')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('from_warehouse_id')
                    ->label('Dari Gudang')
                    ->relationship('fromWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('to_warehouse_id')
                    ->label('Ke Gudang')
                    ->relationship('toWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('delivery_status_id')
                    ->label('Status')
                    ->relationship('status', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('delivery_type')
                    ->label('Tipe Pengiriman')
                    ->options([
                        'regular' => 'Reguler',
                        'urgent' => 'Urgent',
                        'return' => 'Pengembalian',
                    ]),
                Tables\Filters\Filter::make('in_transit')
                    ->label('Dalam Perjalanan')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('status', fn ($q) => 
                            $q->where('code', 'in_transit')
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('change_status')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status_id')
                            ->label('Status Baru')
                            ->options(DeliveryStatus::pluck('name', 'id'))
                            ->required()
                    ])
                    ->action(function (Delivery $record, array $data): void {
                        $oldStatusId = $record->delivery_status_id;
                        $record->update(['delivery_status_id' => $data['status_id']]);
                        
                        $newStatus = DeliveryStatus::find($data['status_id']);
                        
                        // Update related data based on status changes
                        if ($newStatus) {
                            if ($newStatus->code === 'in_transit' && !$record->departure_date) {
                                $record->update(['departure_date' => now()]);
                                
                                // Create VehicleLog for status change
                                $record->vehicle->logs()->create([
                                    'driver_id' => $record->driver_id,
                                    'delivery_id' => $record->id,
                                    'log_time' => now(),
                                    'log_type' => 'delivery_started',
                                    'title' => 'Pengiriman Dimulai',
                                    'note' => "Pengiriman {$record->delivery_code} dalam perjalanan",
                                ]);
                                
                                // Create LedgerFactual entries for source warehouse (outgoing items)
                                // Get the delivery details to get items and quantities
                                $details = $record->details;
                                 
                                foreach ($details as $detail) {
                                    // Find an appropriate rack in the source warehouse
                                    $sourceRack = $record->fromWarehouse->racks()->first();
                                     
                                    if ($sourceRack) {
                                        // Create outgoing ledger entry for the source warehouse
                                        \App\Models\LedgerFactual::create([
                                            'item_id' => $detail->item_id,
                                            'from_rack_id' => $sourceRack->id,
                                            'to_rack_id' => null, // No destination rack for outgoing
                                            'quantity' => $detail->quantity,
                                            'movement_type' => 'outgoing',
                                            'source_id' => $record->id,
                                            'source_type' => 'delivery',
                                            'noted_by' => auth()->id(),
                                            'log_time' => now(),
                                            'note' => "Item keluar untuk pengiriman {$record->delivery_code}",
                                        ]);
                                    }
                                }
                            }
                            
                            if ($newStatus->code === 'arrived' && !$record->arrival_date) {
                                $record->update(['arrival_date' => now()]);
                                
                                // Create VehicleLog for delivery completion
                                $record->vehicle->logs()->create([
                                    'driver_id' => $record->driver_id,
                                    'delivery_id' => $record->id,
                                    'log_time' => now(),
                                    'log_type' => 'delivery_completed',
                                    'title' => 'Pengiriman Selesai',
                                    'note' => "Pengiriman {$record->delivery_code} telah sampai",
                                ]);
                                
                                // Create LedgerFactual entries for destination warehouse (incoming items)
                                // Get the delivery details to get items and quantities
                                $details = $record->details;
                                 
                                foreach ($details as $detail) {
                                    // Find an appropriate rack in the destination warehouse
                                    $destinationRack = $record->toWarehouse->racks()->first();
                                     
                                    if ($destinationRack) {
                                        // Create incoming ledger entry for the destination warehouse
                                        \App\Models\LedgerFactual::create([
                                            'item_id' => $detail->item_id,
                                            'from_rack_id' => null, // No source rack for incoming
                                            'to_rack_id' => $destinationRack->id,
                                            'quantity' => $detail->quantity,
                                            'movement_type' => 'incoming',
                                            'source_id' => $record->id,
                                            'source_type' => 'delivery',
                                            'noted_by' => auth()->id(),
                                            'log_time' => now(),
                                            'note' => "Item masuk dari pengiriman {$record->delivery_code}",
                                        ]);
                                    }
                                }
                            }
                            
                            if ($newStatus->code === 'cancelled') {
                                // Create VehicleLog for delivery cancellation
                                $record->vehicle->logs()->create([
                                    'driver_id' => $record->driver_id,
                                    'delivery_id' => $record->id,
                                    'log_time' => now(),
                                    'log_type' => 'delivery_cancelled',
                                    'title' => 'Pengiriman Dibatalkan',
                                    'note' => "Pengiriman {$record->delivery_code} dibatalkan",
                                ]);
                                
                                // Rollback any LedgerFactual entries related to this delivery
                                // Mark all existing ledger entries as cancelled by adding a note
                                $ledgerEntries = \App\Models\LedgerFactual::where([
                                    'source_id' => $record->id,
                                    'source_type' => 'delivery'
                                ])->get();
                                 
                                foreach ($ledgerEntries as $entry) {
                                    // Create a cancellation entry with opposite movement
                                    $newMovementType = $entry->movement_type === 'incoming' ? 'outgoing' : 'incoming';
                                    $note = "Pembatalan pergerakan {$entry->id} karena pengiriman {$record->delivery_code} dibatalkan";
                                     
                                    \App\Models\LedgerFactual::create([
                                        'item_id' => $entry->item_id,
                                        'from_rack_id' => $entry->to_rack_id, // Swap the racks for reversal
                                        'to_rack_id' => $entry->from_rack_id,
                                        'quantity' => $entry->quantity,
                                        'movement_type' => $newMovementType,
                                        'source_id' => $record->id,
                                        'source_type' => 'delivery_cancellation',
                                        'noted_by' => auth()->id(),
                                        'log_time' => now(),
                                        'note' => $note,
                                    ]);
                                     
                                    // Add cancellation note to original entry
                                    $entry->note = ($entry->note ? $entry->note . "\n" : '') . "Pergerakan dibatalkan pada " . now()->format('d M Y H:i');
                                    $entry->save();
                                }
                            }
                        }
                        
                        Notification::make()
                            ->title('Status diperbarui')
                            ->success()
                            ->send();
                    }),
                    
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
            // Will implement relation managers later
        ];
    }
    
    // Helper function to validate if vehicle has enough capacity for items
    private static function validateVehicleCapacity(Forms\Get $get, Forms\Set $set): void
    {
        $vehicleId = $get('vehicle_id');
        if (!$vehicleId) return;
        
        $vehicle = Vehicle::with('type')->find($vehicleId);
        if (!$vehicle || !$vehicle->type) return;
        
        // Max weight from vehicle type
        $maxWeight = $vehicle->type->max_weight_kg;
        
        // Get delivery details to calculate total weight
        $details = $get('details') ?? [];
        $totalWeight = 0;
        $warnings = [];
        
        // Calculate total weight from all delivery items
        foreach ($details as $index => $detail) {
            if (!isset($detail['item_id']) || !isset($detail['quantity'])) continue;
            
            // Get item weight from database
            $item = \App\Models\Item::find($detail['item_id']);
            if (!$item) continue;
            
            $itemTotalWeight = $item->weight_kg * $detail['quantity'];
            $totalWeight += $itemTotalWeight;
            
            // Check if this single item's weight is close to capacity
            if ($itemTotalWeight > ($maxWeight * 0.7)) {
                $warnings[] = "Item {$item->name} dengan kuantitas {$detail['quantity']} memiliki berat {$itemTotalWeight}kg yang cukup besar dibandingkan kapasitas kendaraan {$maxWeight}kg";
            }
        }
        
        // Set validation state and messages based on weight comparison
        if ($totalWeight > $maxWeight) {
            $set('capacity_warning', "⚠️ KELEBIHAN MUATAN: Total berat {$totalWeight}kg melebihi kapasitas kendaraan {$maxWeight}kg");
            $set('capacity_status', 'overweight');
            
            // Add notification
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Kelebihan Muatan')
                ->body("Total berat muatan melebihi kapasitas kendaraan sebesar " . ($totalWeight - $maxWeight) . "kg")
                ->persistent()
                ->send();
        } 
        elseif ($totalWeight > ($maxWeight * 0.9)) {
            $set('capacity_warning', "⚠️ PERHATIAN: Total berat {$totalWeight}kg hampir mencapai kapasitas kendaraan {$maxWeight}kg");
            $set('capacity_status', 'warning');
            
            // Add notification
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Hampir Mencapai Kapasitas')
                ->body("Total berat muatan hampir mencapai kapasitas kendaraan. Tersisa " . ($maxWeight - $totalWeight) . "kg")
                ->send();
        } 
        else {
            $set('capacity_warning', $totalWeight > 0 ? "✅ Total berat {$totalWeight}kg (Kapasitas tersisa: " . ($maxWeight - $totalWeight) . "kg)" : null);
            $set('capacity_status', 'ok');
        }
        
        // Set additional item-specific warnings if any
        if (!empty($warnings)) {
            $set('item_warnings', $warnings);
        } else {
            $set('item_warnings', []);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }
    
    // Use our access policies to control access based on role
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        // If user has a warehouse_id (indicating they are warehouse admin)
        // only show deliveries related to their warehouse
        if ($user->warehouse_id) {
            $warehouseId = $user->warehouse_id;
            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                  ->orWhere('to_warehouse_id', $warehouseId);
            });
        }
        
        return $query;
    }
}