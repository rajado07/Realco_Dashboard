<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskGenerator extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'market_place_id',
        'type',
        'link',
        'frequency',
        'run_at',
        'status',
    ];
}
