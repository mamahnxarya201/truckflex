<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DeliveryDetail;
use App\Models\Item;
use App\Models\Delivery;

class DeliveryDetailFactory extends Factory
{
    protected $model = DeliveryDetail::class;

    public function definition(): array
    {
        $item = Item::inRandomOrder()->first();

        return [
            'delivery_id' => Delivery::inRandomOrder()->first()?->id ?? 1,
            'item_id' => $item?->id ?? 1,
            'quantity' => fake()->numberBetween(1, 20),
            'unit' => $item?->unit ?? 'pcs',
            'weight_kg' => fake()->randomFloat(2, 1, 100),
            'is_verified' => fake()->boolean(70), // 70% verified
            'note' => fake()->optional()->sentence(),
        ];
    }
}
