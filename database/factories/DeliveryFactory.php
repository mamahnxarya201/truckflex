<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Vehicle;

class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'delivery_code' => 'DLV-' . now()->format('Ymd') . '-' . strtoupper(fake()->bothify('###')),
            'from_warehouse_id' => Warehouse::where('code', 'WH-PST')->first()?->id,
            'to_warehouse_id' => Warehouse::where('code', 'WH-CBG')->first()?->id,
            'driver_id' => User::where('email', 'driver@truckflex.com')->first()?->id,
            'vehicle_id' => Vehicle::inRandomOrder()->first()?->id,
            'validated_by' => User::where('email', 'manager@truckflex.com')->first()?->id,
            'departure_date' => now()->addDays(1), // <-- Fix disini
            'estimated_arrival' => now()->addDays(3),
            'arrival_date' => null, // Masih jalan belum sampai
            'delivery_type' => fake()->randomElement(['internal', 'customer', 'supplier']),
            'delivery_status_id' => 1, // Ganti status_id → delivery_status_id
            'note' => 'Pengiriman awal',
            'is_active' => true,
        ];
    }
}
