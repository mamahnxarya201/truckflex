<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\User;
use Carbon\Carbon;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $motor = VehicleType::firstOrCreate(
            ['name' => 'Motor'],
            [
                'description' => 'Kendaraan roda dua untuk pengiriman kecil',
                'brand' => 'Honda',
                'max_weight_kg' => 150,
                'truck_weight_kg' => 100,
                'fuel_capacity' => 5,
                'fuel_consumption' => 40, // km/liter
                'license_type_required' => 'C',
            ]
        );

        $truk = VehicleType::firstOrCreate(
            ['name' => 'Truk'],
            [
                'description' => 'Truk besar untuk distribusi utama',
                'brand' => 'Isuzu',
                'max_weight_kg' => 8000,
                'truck_weight_kg' => 4000,
                'fuel_capacity' => 120,
                'fuel_consumption' => 7,
                'license_type_required' => 'B2',
            ]
        );

        $van = VehicleType::firstOrCreate(
            ['name' => 'Van'],
            [
                'description' => 'Van untuk pengiriman sedang',
                'brand' => 'Toyota',
                'max_weight_kg' => 1200,
                'truck_weight_kg' => 1000,
                'fuel_capacity' => 50,
                'fuel_consumption' => 12,
                'license_type_required' => 'B1',
            ]
        );


        // Ambil driver
        $driver = User::where('email', 'driver@truckflex.com')->first();

        // Create vehicles
        Vehicle::create([
            'vehicle_type_id' => $motor->id,
            'license_plate' => 'L 1234 ABC',
            'chassis_number' => 'MTR12345678',
            'engine_number' => 'ENG12345678',
            'year' => 2021,
            'color' => 'Merah',
            'is_available' => true,
            'last_maintenance_at' => Carbon::now()->subMonths(2),
            'current_km' => 12000,
            'note' => 'Motor untuk pengantaran ringan',
            'is_active' => true,
        ]);

        Vehicle::create([
            'vehicle_type_id' => $truk->id,
            'license_plate' => 'AD 5678 XYZ',
            'chassis_number' => 'TRK87654321',
            'engine_number' => 'ENG87654321',
            'year' => 2019,
            'color' => 'Putih',
            'is_available' => true,
            'last_maintenance_at' => Carbon::now()->subMonths(5),
            'current_km' => 72000,
            'note' => 'Truk logistik kapasitas besar',
            'is_active' => true,
        ]);

        Vehicle::create([
            'vehicle_type_id' => $van->id,
            'license_plate' => 'L 2178 XYZ',
            'chassis_number' => 'VAN99887766',
            'engine_number' => 'ENG99887766',
            'year' => 2020,
            'color' => 'Silver',
            'is_available' => false,
            'last_maintenance_at' => Carbon::now()->subMonths(1),
            'current_km' => 40500,
            'note' => 'Van untuk pengiriman medium',
            'is_active' => true,
        ]);
    }
}
