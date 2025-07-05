<?php

namespace Database\Factories;

use App\Models\ItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_type_id' => ItemType::factory(),
            'code' => strtoupper(fake()->unique()->bothify('ITM-####')),
            'name' => fake()->word(),
            'unit' => fake()->randomElement(['pcs', 'box', 'kg', 'liter']),
            'weight_kg' => fake()->randomFloat(2, 0.1, 100),
            'cost_price' => fake()->randomFloat(2, 1000, 500000),
            'description' => fake()->sentence(),
        ];
    }
}
