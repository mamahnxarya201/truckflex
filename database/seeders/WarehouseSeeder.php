<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Create or get role reference
        $warehouseManagerRole = Role::where('name', 'warehouse_manager')->first();
        
        // Create Pusat warehouse user with warehouse_manager role
        $pusatUser = User::firstOrCreate(
            ['email' => 'gudangpusat@truckflex.com'],
            [
                'name' => 'Petugas Gudang Pusat',
                'password' => '12345678',
            ]
        );
        
        // Assign warehouse_manager role if it exists
        if ($warehouseManagerRole) {
            $pusatUser->assignRole($warehouseManagerRole);
        }
        
        // Create Pusat warehouse
        $pusatWarehouse = Warehouse::create([
            'code' => 'WH-PST',
            'name' => 'Pusat',
            'description' => 'Gudang pusat utama',
            'address' => 'Jl. Proklamasi No. 1',
            'is_active' => true,
            'type' => 'central',
            'zone' => 'A',
            'manager_id' => $pusatUser->id,
        ]);
        
        // Set user's warehouse_id
        $pusatUser->warehouse_id = $pusatWarehouse->id;
        $pusatUser->save();

        // Create Cabang A warehouse user
        $cabangAUser = User::firstOrCreate(
            ['email' => 'gudanga@truckflex.com'],
            [
                'name' => 'Petugas Gudang Cabang A',
                'password' =>'12345678',
            ]
        );
        
        // Create Cabang A warehouse
        $cabangAWarehouse = Warehouse::create([
            'code' => 'WH-CBG',
            'name' => 'Cabang A',
            'description' => 'Gudang cabang pertama',
            'address' => 'Jl. Raya No. 12',
            'is_active' => true,
            'type' => 'branch',
            'zone' => 'B',
            'manager_id' => $cabangAUser->id,
        ]);
        
        // Set user's warehouse_id
        $cabangAUser->warehouse_id = $cabangAWarehouse->id;
        $cabangAUser->save();
    }
}
