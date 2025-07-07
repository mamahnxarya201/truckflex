<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\DeliveryStatus;
use App\Models\LedgerFactual;
use App\Models\LedgerVirtual;
use App\Models\Rack;
use App\Models\VehicleLog;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;
    
    protected function canEdit(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Get the current delivery
        $delivery = $this->getRecord();
        
        // Using typecasting for clarity when accessing user methods
        /** @var \App\Models\User $user */
        
        // Superadmin can edit any delivery
        if ($user->hasRole('superadmin')) {
            return true;
        }
        
        // Driver cannot edit deliveries
        if ($user->hasRole('driver')) {
            return false;
        }
        
        // Warehouse manager with manage-deliveries permission can edit deliveries related to their warehouse
        if ($user->hasRole('warehouse_manager') && $user->hasPermissionTo('manage-deliveries')) {
            return $user->warehouse_id && (
                $delivery->from_warehouse_id === $user->warehouse_id || 
                $delivery->to_warehouse_id === $user->warehouse_id
            );
        }
        
        return false;
    }

    protected function afterSave(): void
    {
        // Update vehicle log when delivery is updated
        $this->updateVehicleLog($this->record);
        
        // Check if status has changed and create appropriate ledger entries
        $this->handleStatusChange($this->record);
        
        // Show success notification
        Notification::make('updated_delivery')
            ->title('Pengiriman diperbarui')
            ->body('Pengiriman ' . $this->record->delivery_code . ' berhasil diperbarui')
            ->success()
            ->send();
    }
    
    /**
     * Update or create vehicle log entry
     */
    private function updateVehicleLog(Model $record): void
    {
        if (!$record->vehicle_id) return;
        
        $vehicleLogs = VehicleLog::where('delivery_id', $record->id)->get();
        
        if ($vehicleLogs->isEmpty()) {
            // Create new log if none exists
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
        } else {
            // Update existing vehicle logs
            foreach ($vehicleLogs as $log) {
                $log->update([
                    'vehicle_id' => $record->vehicle_id,
                    'driver_id' => $record->driver_id,
                    'log_type' => 'trip',
                    'title' => 'Pengiriman ' . $record->delivery_code,
                    'log_time' => $record->departure_date ?? $log->log_time,
                    'note' => 'Penggunaan untuk pengiriman ' . $record->delivery_code,
                    'is_resolved' => true,
                ]);
            }
        }
    }
    
    /**
     * Handle creation of ledger entries based on status changes
     */
    private function handleStatusChange(Model $record): void
    {
        $status = $record->status;
        if (!$status) return;
        
        // If status is in_transit, create outgoing ledger entries if they don't exist
        if ($status->code === 'in_transit') {
            foreach ($record->details as $detail) {
                // Check if outgoing entry already exists for this item
                $existingOutgoing = LedgerFactual::where([
                    'source_id' => $record->id,
                    'source_type' => 'delivery',
                    'item_id' => $detail->item_id,
                    'movement_type' => 'outgoing',
                ])->exists();
                
                if (!$existingOutgoing) {
                    // Find a suitable rack in the source warehouse
                    $sourceRack = Rack::where('warehouse_id', $record->from_warehouse_id)
                        ->where('is_active', true)
                        ->first();
                        
                    if (!$sourceRack) continue;
                    
                    // Create virtual ledger entry first
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
                    
                    // Create outgoing ledger entry
                    LedgerFactual::create([
                        'item_id' => $detail->item_id,
                        'from_rack_id' => $sourceRack->id,
                        'to_rack_id' => null,
                        'quantity' => -1 * $detail->quantity, // Negative for outbound
                        'movement_type' => "outbound",
                        'source_id' => $virtualEntry->id,
                        'source_type' => "ledger_virtual",
                        'noted_by' => auth()->id(),
                        'log_time' => $record->departure_date ?? now(),
                        'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                    ]);
                }
            }
        }
        
        // If status is arrived, create incoming ledger entries if they don't exist
        if ($status->code === 'arrived') {
            foreach ($record->details as $detail) {
                // Check if outgoing entry already exists for this item
                $existingOutgoing = LedgerFactual::where([
                    'source_id' => $record->id,
                    'source_type' => 'delivery',
                    'item_id' => $detail->item_id,
                    'movement_type' => 'outgoing',
                ])->exists();
                
                if (!$existingOutgoing) {
                    // Need to create outgoing entry first
                    $sourceRack = Rack::where('warehouse_id', $record->from_warehouse_id)
                        ->where('is_active', true)
                        ->first();
                        
                    if ($sourceRack) {
                        // 1. Create virtual ledger entries first
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
                        
                        // 2. Then create factual ledger entries
                        LedgerFactual::create([
                            'item_id' => $detail->item_id,
                            'from_rack_id' => $sourceRack->id,
                            'to_rack_id' => null,
                            'quantity' => -1 * $detail->quantity, // Negative for outbound
                            'movement_type' => "outbound",
                            'source_id' => $outboundVirtual->id,
                            'source_type' => "ledger_virtual",
                            'noted_by' => auth()->id(),
                            'log_time' => $record->departure_date ?? $record->created_at,
                            'note' => 'Keluar dari gudang ' . $record->fromWarehouse->name . ' untuk pengiriman ' . $record->delivery_code,
                        ]);
                        
                        // Create incoming ledger entry
                        $destRack = Rack::where('warehouse_id', $record->to_warehouse_id)
                            ->where('is_active', true)
                            ->first();
                            
                        if ($destRack) {
                            LedgerFactual::create([
                                'item_id' => $detail->item_id,
                                'from_rack_id' => null,
                                'to_rack_id' => $destRack->id,
                                'quantity' => $detail->quantity,
                                'movement_type' => "inbound",
                                'source_id' => $inboundVirtual->id,
                                'source_type' => "ledger_virtual",
                                'noted_by' => auth()->id(),
                                'log_time' => $record->arrival_date ?? now(),
                                'note' => 'Masuk ke gudang ' . $record->toWarehouse->name . ' dari pengiriman ' . $record->delivery_code,
                            ]);
                        }
                    }
                }
                
                // Check if incoming entry already exists for this item
                $existingIncoming = LedgerFactual::where([
                    'source_id' => $record->id,
                    'source_type' => 'delivery',
                    'item_id' => $detail->item_id,
                    'movement_type' => 'incoming',
                ])->exists();
                
                if (!$existingIncoming) {
                    // Create incoming ledger entry
                    $destRack = Rack::where('warehouse_id', $record->to_warehouse_id)
                        ->where('is_active', true)
                        ->first();
                        
                    if ($destRack) {
                        // Create virtual ledger entry first
                        $virtualEntry = LedgerVirtual::create([
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
                        
                        LedgerFactual::create([
                            'item_id' => $detail->item_id,
                            'from_rack_id' => null,
                            'to_rack_id' => $destRack->id,
                            'quantity' => $detail->quantity,
                            'movement_type' => "inbound",
                            'source_id' => $virtualEntry->id,
                            'source_type' => "ledger_virtual",
                            'noted_by' => auth()->id(),
                            'log_time' => $record->arrival_date ?? now(),
                            'note' => 'Masuk ke gudang ' . $record->toWarehouse->name . ' dari pengiriman ' . $record->delivery_code,
                        ]);
                    }
                }
            }
        }
        
        // If status is cancelled, mark ledger entries as cancelled
        if ($status->code === 'cancelled') {
            // Find all ledger entries related to this delivery
            $ledgerEntries = LedgerFactual::where([
                'source_id' => $record->id,
                'source_type' => 'delivery',
            ])->get();
            
            // Update each entry with a cancellation note
            foreach ($ledgerEntries as $entry) {
                $entry->update([
                    'note' => $entry->note . ' [DIBATALKAN: ' . $record->note . ']',
                ]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
