<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'item_id',
        'quantity',
        'unit',
        'weight_kg',
        'is_verified',
        'note',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
