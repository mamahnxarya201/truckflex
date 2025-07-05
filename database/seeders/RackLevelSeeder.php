<?php

namespace Database\Seeders;

use App\Models\RackLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RackLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RackLevel::factory()->count(5)->create();
    }
}
