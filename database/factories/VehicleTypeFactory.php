<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word . ' Truck',
            'description' => $this->faker->sentence(),
            'brand' => $this->faker->randomElement(['Isuzu', 'Hino', 'Mitsubishi', 'Mercedes-Benz']),
            'max_weight_kg' => $this->faker->numberBetween(2000, 10000),
            'truck_weight_kg' => $this->faker->numberBetween(1000, 6000),
            'fuel_capacity' => $this->faker->numberBetween(80, 300),
            'fuel_consumption' => $this->faker->randomFloat(3, 0.1, 0.25),
            'license_type_required' => $this->faker->randomElement(['B1', 'B2']),
        ];
    }
}
