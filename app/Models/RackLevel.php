<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'height_cm',
        'max_load_kg',
        'note',
    ];

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }
}
