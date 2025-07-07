<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerVirtualResource\Pages;
use App\Filament\Resources\LedgerVirtualResource\RelationManagers;
use App\Models\LedgerVirtual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LedgerVirtualResource extends Resource
{
    protected static ?string $model = LedgerVirtual::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationGroup = 'Inventaris';
    
    protected static ?string $navigationLabel = 'Pergerakan Virtual';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pergerakan')
                    ->schema([
                        Forms\Components\Select::make('movement_type')
                            ->label('Jenis Pergerakan')
                            ->options([
                                'incoming' => 'Barang Masuk',
                                'outgoing' => 'Barang Keluar',
                                'transfer' => 'Transfer',
                                'adjustment' => 'Penyesuaian',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Clear warehouse fields based on movement type
                                if ($state === 'incoming') {
                                    $set('from_warehouse_id', null);
                                } elseif ($state === 'outgoing') {
                                    $set('to_warehouse_id', null);
                                }
                            }),
                        
                        Forms\Components\Select::make('item_id')
                            ->label('Item')
                            ->relationship('item', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('from_warehouse_id')
                            ->label('Dari Gudang')
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get) => $get('movement_type') === 'incoming')
                            ->required(fn (Forms\Get $get) => 
                                in_array($get('movement_type'), ['outgoing', 'transfer'])
                            ),
                            
                        Forms\Components\Select::make('to_warehouse_id')
                            ->label('Ke Gudang')
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get) => $get('movement_type') === 'outgoing')
                            ->required(fn (Forms\Get $get) => 
                                in_array($get('movement_type'), ['incoming', 'transfer'])
                            )
                            ->different('from_warehouse_id'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Sumber & Informasi Lainnya')
                    ->schema([
                        Forms\Components\Select::make('source_type')
                            ->label('Tipe Sumber')
                            ->options([
                                'purchase' => 'Pembelian',
                                'sales' => 'Penjualan',
                                'adjustment' => 'Penyesuaian',
                                'transfer' => 'Transfer',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('source_id')
                            ->label('ID Sumber')
                            ->helperText('Nomor referensi atau ID dokumen sumber')
                            ->required()
                            ->numeric(),
                            
                        Forms\Components\Hidden::make('planned_by')
                            ->default(fn () => auth()->id()),
                            
                        Forms\Components\DateTimePicker::make('planned_at')
                            ->label('Waktu Rencana')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.type')
                    ->label('Tipe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->label('Jenis Pergerakan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'incoming' => 'Barang Masuk',
                        'outgoing' => 'Barang Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'incoming' => 'success',
                        'outgoing' => 'danger',
                        'transfer' => 'info',
                        'adjustment' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')
                    ->label('Dari Gudang')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')
                    ->label('Ke Gudang')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Tipe Sumber')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Pembelian',
                        'sales' => 'Penjualan',
                        'adjustment' => 'Penyesuaian',
                        'transfer' => 'Transfer',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_id')
                    ->label('ID Sumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plannedBy.name')
                    ->label('Direncanakan Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('planned_at')
                    ->label('Waktu Rencana')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Jenis Pergerakan')
                    ->options([
                        'incoming' => 'Barang Masuk',
                        'outgoing' => 'Barang Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                    ]),
                Tables\Filters\SelectFilter::make('from_warehouse_id')
                    ->label('Dari Gudang')
                    ->relationship('fromWarehouse', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('to_warehouse_id')
                    ->label('Ke Gudang')
                    ->relationship('toWarehouse', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Tipe Sumber')
                    ->options([
                        'purchase' => 'Pembelian',
                        'sales' => 'Penjualan',
                        'adjustment' => 'Penyesuaian',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\Filter::make('created_today')
                    ->label('Dibuat Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', now())),
            ])
            ->actions([
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
            // No related models needed for now
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        // If user has a warehouse_id (indicating they are warehouse admin)
        // only show ledger entries related to their warehouse
        if ($user->warehouse_id) {
            $warehouseId = $user->warehouse_id;
            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                  ->orWhere('to_warehouse_id', $warehouseId);
            });
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerVirtuals::route('/'),
            'create' => Pages\CreateLedgerVirtual::route('/create'),
            'edit' => Pages\EditLedgerVirtual::route('/{record}/edit'),
        ];
    }
}
