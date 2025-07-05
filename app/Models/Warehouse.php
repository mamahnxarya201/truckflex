<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'is_active',
        'type',
        'manager_id',
        'zone',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class);
    }
}
