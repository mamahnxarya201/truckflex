<?php

namespace Database\Seeders;

use App\Models\DeliveryStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Pending', 'code' => 'pending'],
            ['name' => 'In Transit', 'code' => 'in_transit'],
            ['name' => 'Arrived', 'code' => 'arrived'],
            ['name' => 'Cancelled', 'code' => 'cancelled'],
        ];

        foreach ($statuses as $status) {
            DeliveryStatus::updateOrCreate(['code' => $status['code']], $status);
        }
    }
}
