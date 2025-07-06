<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LedgerVirtual extends Model
{
    use HasFactory;

    protected $table = 'ledger_virtual';

    protected $fillable = [
        'item_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'movement_type',
        'source_type',
        'source_id',
        'planned_by',
        'planned_at',
        'note',
    ];

    protected $casts = [
        'planned_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function source()
    {
        return $this->belongsTo(LedgerVirtual::class, 'source_id');
    }

    public function plannedBy()
    {
        return $this->belongsTo(User::class, 'planned_by');
    }

    // Optional helpers
    public function isInbound(): bool
    {
        return $this->movement_type === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->movement_type === 'outbound';
    }
}
