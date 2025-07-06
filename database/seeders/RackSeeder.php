<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\RackBlock;
use App\Models\RackLevel;
use App\Models\Rack;

class RackSeeder extends Seeder
{
    public function run(): void
    {
        // Clear table (optional if migrate:fresh)
        RackBlock::truncate();
        RackLevel::truncate();
        Rack::truncate();

        // Create Blocks
        $blockA = RackBlock::create([
            'name' => 'Blok A',
            'code' => 'A',
            'forklift_accessible' => true,
        ]);

        $blockB = RackBlock::create([
            'name' => 'Blok B',
            'code' => 'B',
            'forklift_accessible' => false,
        ]);

        // Create Levels
        $level1 = RackLevel::create([
            'name' => 'Level 1',
            'height_cm' => 100,
            'max_load_kg' => 100,
            'note' => "Level plg nyusahno",
        ]);

        $level2 = RackLevel::create([
            'name' => 'Level 2',
            'height_cm' => 200,
            'max_load_kg' => 200,
            'note' => "Level e wong ruwet",
        ]);

        // Assign Racks ke Warehouse
        $warehouses = Warehouse::whereIn('code', ['WH-PST', 'WH-CBG'])->get();

        foreach ($warehouses as $warehouse) {
            foreach (range(1, 5) as $i) {
                Rack::create([
                    'code' => 'RACK-' . $warehouse->code . '-' . $i,
                    'warehouse_id' => $warehouse->id,
                    'rack_block_id' => $i % 2 == 0 ? $blockA->id : $blockB->id,
                    'rack_level_id' => $i % 2 == 0 ? $level1->id : $level2->id,
                    'capacity_kg' => rand(500, 1000),
                    'is_active' => true,
                    'note' => 'Rak ke-' . $i . ' di ' . $warehouse->name,
                ]);
            }
        }
    }
}
