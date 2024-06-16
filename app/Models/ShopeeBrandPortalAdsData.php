<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeBrandPortalAdsData extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_date',
        'shop_name',
        'shop_id',
        'impressions',
        'orders',
        'gross_sales',
        'ads_spend',
        'units_sold',
        'retrieved_at',
        'file_name',
        'brand_id',
        'raw_data_id',
    ];
}
