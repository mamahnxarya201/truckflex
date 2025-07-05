<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

     protected $fillable = [
        'item_type_id',
        'code',
        'name',
        'unit',
        'weight_kg',
        'cost_price',
        'description',
    ];

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
