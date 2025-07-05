<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

     protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
