<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemType;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $type = ItemType::firstOrCreate([
            'name' => 'Suku Cadang',
            'code' => 'SPARE',
            'is_active' => true,
            'description' => 'Jenis item suku cadang kendaraan',
        ]);

        Item::factory()->count(10)->create([
            'item_type_id' => $type->id,
        ]);
    }
}
