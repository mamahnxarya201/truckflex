<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\LedgerFactual;
use App\Models\LedgerVirtual;
use App\Models\Rack;
use App\Models\VehicleLog;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;
    
    protected function canCreate(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */
        
        // Superadmin can create deliveries
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Warehouse manager with manage-deliveries permission can create deliveries
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-deliveries')) {
            return true;
        }
        
        return false;
    }
    
    protected function afterCreate(): void
    {
        // Create vehicle usage log
        $this->createVehicleLog($this->record);
        
        // Create ledger entries based on status
        $this->createLedgerEntries($this->record);
        
        // Show success notification
        Notification::make('created_delivery')
            ->title('Pengiriman dibuat')
            ->body('Pengiriman berhasil dibuat dengan kode: ' . $this->record->delivery_code)
            ->success()
            ->send();
    }
    
    /**
     * Create vehicle usage log entry
     */
    private function createVehicleLog(Model $record): void
    {
        if (!$record->vehicle_id) return;
        
        // Create vehicle usage log
        VehicleLog::create([
            'vehicle_id' => $record->vehicle_id,
            'driver_id' => $record->driver_id,
            'delivery_id' => $record->id,
            'log_type' => 'trip',
            'title' => 'Pengiriman ' . $record->delivery_code,
            'note' => 'Penggunaan untuk pengiriman ' . $record->delivery_code,
            'log_time' => $record->departure_date ?? now(),
            'is_resolved' => true,
        ]);
    }
    
    /**
     * Create ledger entries based on delivery status
     */
    private function createLedgerEntries(Model $record): void
    {
        $status = $record->status;
        if (!$status) return;
        
        if ($status->code === 'in_transit') {
            // Create outgoing ledger entries
            foreach ($record->details as $detail) {
                // Find a suitable rack in the source warehouse
                $sourceRack = Rack::where('warehouse_id', $record->from_warehouse_id)
                    ->where('is_active', true)
                    ->first();
                    
                if (!$sourceRack) continue;
                
                // 1. First create a virtual ledger entry
                $virtualEntry = LedgerVirtual::create([
                    'item_id' => $detail->item_id,
                    'from_warehouse_id' => $record->from_warehouse_id,
                    'to_warehouse_id' => null,
                    'quantity' => -1 * $detail->quantity, // Negative for outbound
                    'movement_type' => "outbound",
                    'source_type' => "delivery",
                    'source_id' => $record->id,
                    'planned_by' => auth()->id(),
                    'planned_at' => now(),
                    'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                ]);
                
                // 2. Then create outgoing factual ledger entry using the virtual entry as source
                LedgerFactual::create([
                    'item_id' => $detail->item_id,
                    'from_rack_id' => $sourceRack->id,
                    'to_rack_id' => null,
                    'quantity' => -1 * $detail->quantity, // Negative for outbound
                    'movement_type' => "outbound",
                    'source_id' => $virtualEntry->id, // Reference the virtual ledger entry
                    'source_type' => "ledger_virtual",
                    'noted_by' => auth()->id(),
                    'log_time' => $record->departure_date ?? now(),
                    'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                ]);
            }
        }
        
        if ($status->code === 'arrived') {
            // Create both outgoing and incoming ledger entries
            foreach ($record->details as $detail) {
                // Find suitable racks in source and destination warehouses
                $sourceRack = Rack::where('warehouse_id', $record->from_warehouse_id)
                    ->where('is_active', true)
                    ->first();
                    
                $destRack = Rack::where('warehouse_id', $record->to_warehouse_id)
                    ->where('is_active', true)
                    ->first();
                    
                if (!$sourceRack || !$destRack) continue;
                
                // 1. First create virtual ledger entries
                $outboundVirtual = LedgerVirtual::create([
                    'item_id' => $detail->item_id,
                    'from_warehouse_id' => $record->from_warehouse_id,
                    'to_warehouse_id' => null,
                    'quantity' => -1 * $detail->quantity, // Negative for outbound
                    'movement_type' => "outbound",
                    'source_type' => "delivery",
                    'source_id' => $record->id,
                    'planned_by' => auth()->id(),
                    'planned_at' => now(),
                    'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                ]);
                
                $inboundVirtual = LedgerVirtual::create([
                    'item_id' => $detail->item_id,
                    'from_warehouse_id' => null,
                    'to_warehouse_id' => $record->to_warehouse_id,
                    'quantity' => $detail->quantity,
                    'movement_type' => "inbound",
                    'source_type' => "delivery",
                    'source_id' => $record->id,
                    'planned_by' => auth()->id(),
                    'planned_at' => now(),
                    'note' => 'Masuk ke gudang ' . $record->toWarehouse->name . ' dari pengiriman ' . $record->delivery_code,
                ]);
                
                // 2. Then create factual ledger entries referencing the virtual ones
                LedgerFactual::create([
                    'item_id' => $detail->item_id,
                    'from_rack_id' => $sourceRack->id,
                    'to_rack_id' => null,
                    'quantity' => -1 * $detail->quantity, // Negative for outbound
                    'movement_type' => "outbound",
                    'source_id' => $outboundVirtual->id, // Reference the virtual ledger entry
                    'source_type' => "ledger_virtual",
                    'noted_by' => auth()->id(),
                    'log_time' => $record->departure_date ?? $record->created_at,
                    'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                ]);
                
                // Create incoming ledger entry
                LedgerFactual::create([
                    'item_id' => $detail->item_id,
                    'from_rack_id' => null,
                    'to_rack_id' => $destRack->id,
                    'quantity' => $detail->quantity,
                    'movement_type' => "inbound",
                    'source_id' => $inboundVirtual->id, // Reference the virtual ledger entry
                    'source_type' => "ledger_virtual",
                    'noted_by' => auth()->id(),
                    'log_time' => $record->arrival_date ?? now(),
                    'note' => 'Masuk ke gudang ' . $record->toWarehouse->name . ' dari pengiriman ' . $record->delivery_code,
                ]);
            }
        }
    }
}
