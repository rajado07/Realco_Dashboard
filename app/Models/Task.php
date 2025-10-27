<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

}
