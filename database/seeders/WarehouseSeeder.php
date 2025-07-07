<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Pusat warehouse user
        $pusatUser = User::firstOrCreate(
            ['email' => 'gudangpusat@flextruck.com'],
            [
                'name' => 'Petugas Gudang Pusat',
                'password' => '12345678',
            ]
        );
        
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
            ['email' => 'gudanga@flextruck.com'],
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
