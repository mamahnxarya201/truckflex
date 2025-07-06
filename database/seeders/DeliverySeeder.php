<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Delivery;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        Delivery::factory()->count(2)->create();
    }
}
