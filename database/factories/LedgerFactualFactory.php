<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\Rack;
use App\Models\User;
use App\Models\Warehouse;

class LedgerFactualFactory extends Factory
{
    public function definition(): array
    {
        $item = Item::inRandomOrder()->first();
        $toWarehouse = Warehouse::where('code', 'WH-CBG')->first();
        $rack = Rack::where('warehouse_id', $toWarehouse->id)->inRandomOrder()->first();

        return [
            'item_id' => $item->id,
            'from_warehouse_id' => Warehouse::where('code', 'WH-PST')->first()?->id,
            'to_warehouse_id' => $toWarehouse->id,
            'rack_id' => $rack->id,
            'quantity' => $qty = fake()->numberBetween(1, 10),
            'movement_type' => 'transfer',
            'source_type' => 'manual',
            'source_id' => null,
            'verified_by' => User::where('email', 'manager@pusat.com')->first()?->id,
            'verified_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}