<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RackLevel>
 */
class RackLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Level ' . $this->faker->randomDigitNotZero(),
            'height_cm' => $this->faker->numberBetween(100, 300),
            'max_load_kg' => $this->faker->randomFloat(2, 100, 1000),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
