<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LedgerVirtual;
use App\Models\LedgerFactual;
use App\Models\Delivery;
use App\Models\Item;
use App\Models\Rack;
use App\Models\Warehouse;
use App\Models\User;

class LedgerSeeder extends Seeder
{
    public function run(): void
    {
        $deliveries = Delivery::all();
        $items = Item::all();
        $manager = User::where('email', 'manager@truckflex.com')->first();
        $toWarehouse = Warehouse::where('code', 'WH-CBG')->first();

        foreach ($deliveries as $delivery) {
            foreach ($items->random(3) as $item) {
                $virtual = LedgerVirtual::create([
                    'item_id' => $item->id,
                    'from_warehouse_id' => $delivery->from_warehouse_id,
                    'to_warehouse_id' => $delivery->to_warehouse_id,
                    'quantity' => $qty = rand(1, 10),
                    'movement_type' => 'transfer',
                    'source_type' => 'delivery',
                    'source_id' => $delivery->id,
                    'planned_by' => $delivery->validated_by,
                    'planned_at' => now(),
                    'note' => 'Ledger virtual dari delivery #' . $delivery->id,
                ]);

                $rack = Rack::where('warehouse_id', $toWarehouse->id)->inRandomOrder()->first();

                LedgerFactual::create([
                    'item_id' => $item->id,
                    'from_rack_id' => $fromRack->id ?? null,
                    'to_rack_id' => $rack->id, // ini rack tujuan
                    'quantity' => $qty,
                    'movement_type' => 'transfer',
                    'source_type' => 'ledger_virtual',
                    'source_id' => $virtual->id,
                    'noted_by' => $manager->id,
                    'log_time' => now(),
                    'note' => 'Ledger factual dari ledger virtual #' . $virtual->id,
                    'verified_at' => now(),
                ]);
            }
        }
    }
}
