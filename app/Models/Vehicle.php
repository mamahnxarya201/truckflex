<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_type_id',
        'license_plate',
        'chassis_number',
        'engine_number',
        'year',
        'color',
        'is_available',
        'last_maintenance_at',
        'current_km',
        'note',
        'is_active',
    ];

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function logs()
    {
        return $this->hasMany(VehicleLog::class);
    }
}