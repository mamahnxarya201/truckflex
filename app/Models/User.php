<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'warehouse_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function vehicleLogs()
    {
        return $this->hasMany(VehicleLog::class, 'driver_id');
    }

    public function deliveriesDriven()
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function deliveriesValidated()
    {
        return $this->hasMany(Delivery::class, 'validated_by');
    }
    
    /**
     * Get the warehouse that the user is assigned to (for warehouse admins)
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Check if user is a warehouse admin
     */
    public function isWarehouseAdmin(): bool
    {
        return $this->hasRole('warehouse_admin');
    }
    
    /**
     * Check if user can access specific warehouse
     */
    public function canAccessWarehouse(int $warehouseId): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }
        
        if ($this->isWarehouseAdmin()) {
            return $this->warehouse_id === $warehouseId;
        }
        
        return false;
    }
}
