<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaCpasData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function dataGroup()
    {
        return $this->belongsTo(DataGroup::class, 'data_group_id');
    }
}
