<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'forklift_accessible',
        'note',
    ];

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }
}
