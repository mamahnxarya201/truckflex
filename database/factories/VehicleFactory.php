<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_type_id' => VehicleType::factory(),
            'license_plate' => strtoupper(fake()->bothify('B #### ??')),
            'chassis_number' => fake()->uuid(),
            'engine_number' => fake()->uuid(),
            'year' => fake()->year(),
            'color' => fake()->safeColorName(),
            'is_available' => true,
            'last_maintenance_at' => now()->subDays(rand(10, 100)),
            'current_km' => rand(10000, 200000),
            'note' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
