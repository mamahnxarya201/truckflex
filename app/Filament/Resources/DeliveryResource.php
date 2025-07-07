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
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        if (!$user) {
            return $query;
        }
        
        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */
        
        // Driver can only see deliveries assigned to them
        if ($user->hasRole('driver')) {
            return $query->where('driver_id', $user->id);
        }
        
        // Warehouse manager can only see deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager') && $user->warehouse_id) {
            return $query->where(function($query) use ($user) {
                $query->where('from_warehouse_id', $user->warehouse_id)
                      ->orWhere('to_warehouse_id', $user->warehouse_id);
            });
        }
        
        // Superadmin sees all deliveries
        return $query;
    }
    
    public static function getNavigationBadge(): ?string
    {
        $query = static::getEloquentQuery();
        
        return $query->where('delivery_status_id', DeliveryStatus::where('code', 'in_transit')->first()?->id ?? 0)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden field to track if this is an initial creation
                Forms\Components\Hidden::make('is_new_record')
                    ->default(true),
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_code')
                            ->label('Kode Pengiriman')
                            // ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Otomatis jika kosong')
                            ->helperText('Kode unik untuk pengiriman ini'),
                            
                        Forms\Components\Select::make('delivery_type')
                            ->label('Tipe Pengiriman')
                            ->options([
                                'internal' => 'Internal',
                                'customer' => 'Pelanggan',
                                'supplier' => 'Pemasok',
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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Clear any selected items when warehouse changes
                                $set('details', []);
                                
                                // Reset capacity warnings
                                $set('capacity_warning', null);
                                $set('capacity_status', 'ok');
                                $set('item_warnings', []);
                            }),
                            
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
                
                Forms\Components\Section::make('Item Pengiriman')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->label('Detail Item')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (Forms\Get $get, ?Model $record) {
                                        $warehouseId = $get('../../from_warehouse_id');
                                        if (!$warehouseId) {
                                            return [];
                                        }
                                        
                                        // Direct query to calculate available stock in warehouse
                                        // First get all racks in the warehouse
                                        $warehouseRacks = \App\Models\Rack::where('warehouse_id', $warehouseId)
                                            ->where('is_active', true)
                                            ->pluck('id')
                                            ->toArray();
                                            
                                        if (empty($warehouseRacks)) {
                                            return [];
                                        }
                                        
                                        // Calculate stock levels using SQL query
                                        $stockItems = \Illuminate\Support\Facades\DB::select(
                                            "SELECT 
                                                i.id as item_id,
                                                i.name as item_name,
                                                SUM(CASE 
                                                    WHEN lf.to_rack_id IN (" . implode(',', $warehouseRacks) . ") THEN lf.quantity 
                                                    WHEN lf.from_rack_id IN (" . implode(',', $warehouseRacks) . ") THEN -lf.quantity 
                                                    ELSE 0 
                                                END) as stock_quantity
                                            FROM 
                                                items i
                                            LEFT JOIN 
                                                ledger_factual lf ON i.id = lf.item_id
                                            GROUP BY 
                                                i.id, i.name
                                            HAVING 
                                                stock_quantity > 0"
                                        );
                                        
                                        // Format as options array for the dropdown
                                        $items = collect($stockItems)->pluck('item_name', 'item_id')->toArray();
                                        
                                        // We'll store stock quantities in the session for validation purposes
                                        session(['warehouse_'.$warehouseId.'_stock' => collect($stockItems)->pluck('stock_quantity', 'item_id')->toArray()]);
                                        
                                        return $items;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (!$state) return;
                                        
                                        $item = \App\Models\Item::find($state);
                                        if (!$item) return;
                                        
                                        // Set the unit from the item
                                        $set('unit', $item->unit);
                                        
                                        // Set default quantity to 1 (or reset if changed)
                                        $set('quantity', 1);
                                        
                                        // Set the weight based on item weight and default quantity
                                        $weight = $item->weight_kg * 1;
                                        $set('weight_kg', $weight);
                                        
                                        // Get available stock for this item from session
                                        $warehouseId = $get('../../from_warehouse_id');
                                        $stockQuantities = session('warehouse_'.$warehouseId.'_stock', []);
                                        $availableStock = $stockQuantities[$state] ?? 0;
                                        
                                        // Store max available quantity for validation
                                        $set('max_available', $availableStock);
                                    }),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->rules([
                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $maxAvailable = $get('max_available') ?? 0;
                                            if ($value > $maxAvailable) {
                                                $fail("Jumlah melebihi stok tersedia ({$maxAvailable})");
                                            }
                                        },
                                    ])
                                    ->helperText(fn (Forms\Get $get) => 'Stok tersedia: ' . ($get('max_available') ?? 0))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) return;
                                        
                                        $item = \App\Models\Item::find($itemId);
                                        if (!$item) return;
                                        
                                        // Update weight based on new quantity
                                        $weight = $item->weight_kg * $state;
                                        $set('weight_kg', $weight);
                                        
                                        // We'll use a global event to trigger validation at the form level
                                        // This avoids issues with trying to access parent fields directly
                                        // Item weight update will be handled at form level
                                    }),
                                    
                                Forms\Components\TextInput::make('unit')
                                    ->label('Unit')
                                    ->disabled()
                                    ->dehydrated(),
                                    
                                Forms\Components\TextInput::make('weight_kg')
                                    ->label('Berat (kg)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Berat dikalkulasi otomatis dari item dan jumlah'),
                                    
                                Forms\Components\Textarea::make('note')
                                    ->label('Catatan Item')
                                    ->placeholder('Catatan untuk item ini')
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                // We can call validateVehicleCapacity directly here at the form level
                                self::validateVehicleCapacity($get, $set);
                            })
                            ->live()
                    ])
                    ->columnSpanFull(),
                    
                Forms\Components\Section::make('Informasi Kapasitas')
                    ->schema([
                        Forms\Components\Placeholder::make('capacity_warning')
                            ->label('Status Kapasitas')
                            ->content(function (Forms\Get $get) {
                                return $get('capacity_warning') ?? 'Belum ada item dipilih';
                            })
                            ->columnSpanFull(),
                            
                        Forms\Components\Hidden::make('capacity_warning'),
                        Forms\Components\Hidden::make('capacity_status')
                            ->default('ok'),
                        Forms\Components\Hidden::make('item_warnings'),
                    ])
                    ->hidden(fn (Forms\Get $get) => !$get('vehicle_id'))
                    ->columnSpanFull(),
                    
                Forms\Components\Textarea::make('note')
                    ->label('Catatan Pengiriman')
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
                    
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->badge()
                    ->color('success'),
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
    
    /**
     * Create vehicle usage log entry
     */
    private static function createVehicleLog(Model $record): void
    {
        if (!$record->vehicle_id) return;
        
        // Create vehicle usage log
        \App\Models\VehicleLog::create([
            'vehicle_id' => $record->vehicle_id,
            'driver_id' => $record->driver_id,
            'delivery_id' => $record->id,
            'start_time' => $record->departure_date,
            'end_time' => $record->arrival_date,
            'distance_km' => $record->distance_km ?? 0,
            'fuel_usage_liters' => $record->fuel_usage ?? 0,
            'note' => "Penggunaan untuk pengiriman {$record->delivery_code}",
            'created_by' => auth()->id(),
        ]);
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
            
            $itemTotalWeight = ($detail['weight_kg'] ?? ($item->weight_kg * $detail['quantity']));
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
}