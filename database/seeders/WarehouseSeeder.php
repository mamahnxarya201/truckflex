<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'manager@truckflex.com')->first();

        Warehouse::create([
            'code' => 'WH-PST',
            'name' => 'Pusat',
            'description' => 'Gudang pusat utama',
            'address' => 'Jl. Proklamasi No. 1',
            'is_active' => true,
            'type' => 'central',
            'zone' => 'A',
            'manager_id' => $manager->id,
        ]);

        Warehouse::create([
            'code' => 'WH-CBG',
            'name' => 'Cabang A',
            'description' => 'Gudang cabang pertama',
            'address' => 'Jl. Raya No. 12',
            'is_active' => true,
            'type' => 'branch',
            'zone' => 'B',
            'manager_id' => $manager->id,
        ]);
    }
}
