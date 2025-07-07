<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerFactualResource\Pages;
use App\Filament\Resources\LedgerFactualResource\RelationManagers;
use App\Models\LedgerFactual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LedgerFactualResource extends Resource
{
    protected static ?string $model = LedgerFactual::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $navigationGroup = 'Inventaris';
    
    protected static ?string $navigationLabel = 'Pergerakan Fisik';
    
    protected static ?int $navigationSort = 3;

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
                                // Clear rack fields based on movement type
                                if ($state === 'incoming') {
                                    $set('from_rack_id', null);
                                } elseif ($state === 'outgoing') {
                                    $set('to_rack_id', null);
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
                
                Forms\Components\Section::make('Lokasi Rak')
                    ->schema([
                        Forms\Components\Select::make('from_rack_id')
                            ->label('Dari Rak')
                            ->relationship('fromRack', 'name', function ($query, $get) {
                                // Only show racks from the appropriate warehouse
                                $user = auth()->user();
                                if ($user->warehouse_id) {
                                    $query->whereHas('warehouse', function ($q) use ($user) {
                                        $q->where('id', $user->warehouse_id);
                                    });
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get) => $get('movement_type') === 'incoming')
                            ->required(fn (Forms\Get $get) => 
                                in_array($get('movement_type'), ['outgoing', 'transfer'])
                            ),
                            
                        Forms\Components\Select::make('to_rack_id')
                            ->label('Ke Rak')
                            ->relationship('toRack', 'name', function ($query, $get) {
                                // Only show racks from the appropriate warehouse
                                $user = auth()->user();
                                if ($user->warehouse_id) {
                                    $query->whereHas('warehouse', function ($q) use ($user) {
                                        $q->where('id', $user->warehouse_id);
                                    });
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get) => $get('movement_type') === 'outgoing')
                            ->required(fn (Forms\Get $get) => 
                                in_array($get('movement_type'), ['incoming', 'transfer'])
                            )
                            ->different('from_rack_id'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Referensi & Informasi Pencatatan')
                    ->schema([
                        Forms\Components\Select::make('source_type')
                            ->label('Tipe Sumber')
                            ->options([
                                'delivery' => 'Pengiriman',
                                'adjustment' => 'Penyesuaian',
                                'transfer' => 'Transfer',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('source_id')
                            ->label('ID Sumber')
                            ->helperText('Nomor referensi atau ID dokumen sumber')
                            ->required()
                            ->numeric(),
                        
                        Forms\Components\Hidden::make('noted_by')
                            ->default(fn () => auth()->id()),
                            
                        Forms\Components\DateTimePicker::make('log_time')
                            ->label('Waktu Pencatatan')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Waktu Verifikasi')
                            ->helperText('Kosongkan jika belum diverifikasi'),
                            
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
                Tables\Columns\TextColumn::make('fromRack.name')
                    ->label('Dari Rak')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('toRack.name')
                    ->label('Ke Rak')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fromRack.warehouse.name')
                    ->label('Gudang Asal')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('toRack.warehouse.name')
                    ->label('Gudang Tujuan')
                    ->placeholder('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Tipe Sumber')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'delivery' => 'Pengiriman',
                        'adjustment' => 'Penyesuaian',
                        'transfer' => 'Transfer',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_id')
                    ->label('ID Sumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notedBy.name')
                    ->label('Dicatat Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('log_time')
                    ->label('Waktu Pencatatan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('verified_at')
                    ->label('Diverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (LedgerFactual $record): bool => $record->verified_at !== null),
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
                Tables\Filters\Filter::make('verified')
                    ->label('Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('verified_at')),
                Tables\Filters\Filter::make('not_verified')
                    ->label('Belum Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNull('verified_at')),
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Tipe Sumber')
                    ->options([
                        'delivery' => 'Pengiriman',
                        'adjustment' => 'Penyesuaian',
                        'transfer' => 'Transfer',
                    ]),
                Tables\Filters\Filter::make('created_today')
                    ->label('Dibuat Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (LedgerFactual $record): bool => $record->verified_at === null)
                    ->action(function (LedgerFactual $record) {
                        $record->verified_at = now();
                        $record->save();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_verify')
                        ->label('Verifikasi Massal')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->verified_at === null) {
                                    $record->verified_at = now();
                                    $record->save();
                                }
                            });
                        }),
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
            $query->whereHas('fromRack', function ($q) use ($warehouseId) {
                $q->whereHas('warehouse', function ($q2) use ($warehouseId) {
                    $q2->where('id', $warehouseId);
                });
            })->orWhereHas('toRack', function ($q) use ($warehouseId) {
                $q->whereHas('warehouse', function ($q2) use ($warehouseId) {
                    $q2->where('id', $warehouseId);
                });
            });
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerFactuals::route('/'),
            'create' => Pages\CreateLedgerFactual::route('/create'),
            'edit' => Pages\EditLedgerFactual::route('/{record}/edit'),
        ];
    }
}
