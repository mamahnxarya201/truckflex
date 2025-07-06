<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\Delivery;

class LedgerVirtualFactory extends Factory
{
    public function definition(): array
    {
        $item = Item::inRandomOrder()->first();
        $delivery = Delivery::inRandomOrder()->first();

        return [
            'item_id' => $item->id,
            'from_warehouse_id' => $delivery->from_warehouse_id,
            'to_warehouse_id' => $delivery->to_warehouse_id,
            'quantity' => $qty = fake()->numberBetween(1, 10),
            'movement_type' => 'transfer',
            'source_type' => 'delivery',
            'source_id' => $delivery->id,
            'planned_by' => $delivery->validated_by,
            'planned_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
