<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use App\Models\User;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'Catatan Kendaraan';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->options(fn() => User::query()->limit(5)->pluck('name', 'id'))
                    ->relationship('driver', 'name')
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
                    ->relationship('delivery', 'id')
                    ->options(fn() => Delivery::query()->limit(5)->pluck('id', 'id'))
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
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
                    
                Tables\Filters\Filter::make('log_time')
                    ->form([
                        Forms\Components\DatePicker::make('log_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('log_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['log_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('log_time', '>=', $date),
                            )
                            ->when(
                                $data['log_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('log_time', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
