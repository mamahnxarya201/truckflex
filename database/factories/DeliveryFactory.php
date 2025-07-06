<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_code' => 'DLV-' . strtoupper(fake()->bothify('###??')),
            'from_warehouse_id' => \App\Models\Warehouse::inRandomOrder()->first()?->id ?? 1,
            'to_warehouse_id' => \App\Models\Warehouse::inRandomOrder()->first()?->id ?? 2,
            'driver_id' => \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'driver'))->inRandomOrder()->first()?->id ?? 1,
            'vehicle_id' => \App\Models\Vehicle::inRandomOrder()->first()?->id ?? 1,
            'delivery_status_id' => \App\Models\DeliveryStatus::inRandomOrder()->first()?->id ?? 1,
            'delivery_type' => fake()->randomElement(['internal', 'customer', 'supplier']),
            'validated_by' => \App\Models\User::inRandomOrder()->first()?->id ?? null,
            'departure_date' => now()->subDays(rand(0, 5)),
            'estimated_arrival' => now()->addDays(rand(1, 3)),
            'arrival_date' => rand(0, 1) ? now()->addDays(rand(2, 5)) : null,
            'note' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
