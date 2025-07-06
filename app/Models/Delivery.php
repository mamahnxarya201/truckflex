<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_code',
        'from_warehouse_id',
        'to_warehouse_id',
        'driver_id',
        'vehicle_id',
        'delivery_status_id',
        'delivery_type',
        'validated_by',
        'departure_date',
        'estimated_arrival',
        'arrival_date',
        'note',
        'is_active',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function status()
    {
        return $this->belongsTo(DeliveryStatus::class, 'delivery_status_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function details()
    {
        return $this->hasMany(DeliveryDetail::class);
    }
    
    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetail::class);
    }
}
