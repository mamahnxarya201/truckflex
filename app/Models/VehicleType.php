<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
        'description',
        'brand',
        'max_weight_kg',
        'truck_weight_kg',
        'fuel_capacity',
        'fuel_consumption',
        'license_type_required',
    ];
}
