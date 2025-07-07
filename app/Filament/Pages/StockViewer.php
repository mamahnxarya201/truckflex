<?php

namespace App\Filament\Pages;

use App\Models\StockSummary;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockViewer extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    
    /**
     * Check if the current user can access this page.
     */
    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole('superadmin') || ($user && $user->hasRole('warehouse_manager'));
    }

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string $view = 'filament.pages.stock-viewer';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?string $title = 'Stock Barang';

    public static function table(Table $table): Table
    {
        $query = StockSummary::query();
        
        // Filter by warehouse if user is logged in and has a warehouse_id
        $user = Auth::user();
        
        // Only superadmins can see all warehouses, others see only their own warehouse
        if ($user && $user->warehouse_id) {
            // Check if the user is a superadmin (who should see all warehouses)
            $isSuperadmin = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('roles.name', 'superadmin')
                ->exists();
            
            // If not a superadmin, filter by warehouse_id
            if (!$isSuperadmin) {
                $query->where('warehouse_id', $user->warehouse_id);
            }
        }
        
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
            ->query($query);
    }
}

