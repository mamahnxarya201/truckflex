<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Delivery;
use App\Models\Item;

class DeliveryDetailFactory extends Factory
{
    public function definition(): array
    {
        $item = Item::inRandomOrder()->first();

        return [
            'delivery_id' => Delivery::inRandomOrder()->first()?->id,
            'item_id' => $item?->id,
            'quantity' => $qty = fake()->numberBetween(1, 20),
            'unit' => $item?->unit ?? 'pcs',
            'weight_kg' => $item?->weight_kg * $qty,
            'is_verified' => fake()->boolean(80), // default 80% true
            'note' => fake()->optional()->sentence(),
        ];
    }
}
