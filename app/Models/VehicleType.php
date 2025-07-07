<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
