<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'brand_id',
    //     'market_place_id',
    //     'type',
    //     'link',
    //     'scheduled_to_run',
    //     'status',
    //     'task_generator_id',
    //     'message',
    // ];

    protected $guarded = ['id'];

}
