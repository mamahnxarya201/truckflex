<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LedgerFactual extends Model
{
    use HasFactory;

    protected $table = 'ledger_factual';

    protected $fillable = [
        'item_id',
        'from_rack_id',
        'to_rack_id',
        'quantity',
        'movement_type',
        'source_type',
        'source_id',
        'noted_by',
        'log_time',
        'note',
        'verified_at',
    ];

    protected $casts = [
        'log_time' => 'datetime',
        'verified_at' => 'datetime',
        'movement_type' => 'string',
        'source_type' => 'string',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function fromRack()
    {
        return $this->belongsTo(Rack::class, 'from_rack_id');
    }

    public function toRack()
    {
        return $this->belongsTo(Rack::class, 'to_rack_id');
    }

    public function source()
    {
        return $this->belongsTo(Delivery::class, 'source_id');
    }

    public function notedBy()
    {
        return $this->belongsTo(User::class, 'noted_by');
    }

    public function isTransfer(): bool
    {
        return $this->movement_type === 'transfer';
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }
}
