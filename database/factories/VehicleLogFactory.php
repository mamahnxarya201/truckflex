<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleLog>
 */
class VehicleLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['trip', 'fuel', 'maintenance', 'incident']);
        $titles = [
            'trip' => 'Perjalanan ke lokasi tujuan',
            'fuel' => 'Isi BBM di SPBU 34-001',
            'maintenance' => 'Ganti Oli & Rem',
            'incident' => 'Ban pecah di KM 53',
        ];

        return [
            'vehicle_id' => \App\Models\Vehicle::inRandomOrder()->first()?->id ?? 1,
            'driver_id' => \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'driver'))->inRandomOrder()->first()?->id ?? 1,
            'delivery_id' => \App\Models\Delivery::inRandomOrder()->first()?->id ?? null,
            'log_type' => $type,
            'title' => $titles[$type],
            'note' => fake()->paragraph(),
            'log_time' => fake()->dateTimeBetween('-1 week', 'now'),
            'is_resolved' => fake()->boolean(85),
        ];
    }
}
