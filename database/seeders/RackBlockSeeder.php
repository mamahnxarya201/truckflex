<?php

namespace Database\Seeders;

use App\Models\RackBlock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RackBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RackBlock::factory()->count(5)->create();
    }
}
