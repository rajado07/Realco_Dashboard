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

    // Recusive Relationship Model

    public function parent()
    {
        return $this->belongsTo(DataGroup::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DataGroup::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }
}
