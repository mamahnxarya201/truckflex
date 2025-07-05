<?php

namespace Database\Factories;

use App\Models\RackBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RackBlock>
 */
class RackBlockFactory extends Factory
{
    protected $model = RackBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->bothify('B##')),
            'name' => 'Zona ' . $this->faker->randomLetter(),
            'forklift_accessible' => $this->faker->boolean(70),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
