<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeBrandPortalShopData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(DataGroup::class, 'data_group_id');
    }

}
