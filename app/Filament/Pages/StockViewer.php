<?php

namespace App\Filament\Pages;

use App\Models\StockSummary;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StockViewer extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.stock-viewer';

    protected static ?string $title = 'Stock Viewer';

    public static function table(Table $table): Table
    {
        // dd(StockSummary::query());
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')->label('Item'),
                Tables\Columns\TextColumn::make('item_type')->label('Tipe'),
                Tables\Columns\TextColumn::make('warehouse_name')->label('Gudang'),
                Tables\Columns\TextColumn::make('qty_virtual')->numeric()->label('Qty Sistem'),
                Tables\Columns\TextColumn::make('qty_factual')->numeric()->label('Qty Lapangan'),
            ])
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([])
            ->query(StockSummary::query());
    }
}

