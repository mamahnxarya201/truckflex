<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('GD-?')),
            'name' => 'Gudang ' . fake()->city(),
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'is_active' => true,
            'type' => fake()->randomElement(['main', 'transit', 'retur']),
            'manager_id' => \App\Models\User::factory(),
            'zone' => fake()->randomElement(['UTARA', 'SELATAN', 'TIMUR']),
        ];
    }
}
