<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeBrandPortalShopData extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_id',
        'gross_sales',
        'gross_orders',
        'gross_units_sold',
        'product_views',
        'product_visitors',
        'retrieved_at',
        'data_date',
        'file_name',
        'brand_id',
        'raw_data_id',
    ];

}
