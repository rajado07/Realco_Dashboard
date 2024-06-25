<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawData extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'type',
    //     'data',
    //     'retrieved_at',
    //     'data_date',
    //     'file_name',
    //     'brand_id',
    //     'market_place_id',
    //     'task_id',
    //     'status',
    //     'message',
    // ];

    protected $guarded = ['id'];

}
