<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataGroup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function products()
    {
        return $this->hasMany(ShopeeBrandPortalShopData::class, 'data_group_id');
    }

    public function shopeeBrandPortalShopData()
    {
        return $this->hasMany(ShopeeBrandPortalShopData::class, 'data_group_id');
    }
}
