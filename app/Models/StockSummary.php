<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockSummary extends Model
{
    use HasFactory;

     public $table = 'stock_summary_view';

    public $timestamps = false; // karena ini view

    protected $guarded = [];
}
