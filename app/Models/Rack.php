<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rack extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'rack_level_id',
        'rack_block_id',
        'code',
        'capacity_kg',
        'is_active',
        'note',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(RackLevel::class, 'rack_level_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(RackBlock::class, 'rack_block_id');
    }
}
