<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaCpasData extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_date',
        'ad_set_name',
        'ad_set_id',
        'amount_spent',
        'content_views_with_shared_items',
        'adds_to_cart_with_shared_items',
        'purchases_with_shared_items',
        'purchases_conversion_value_for_shared_items_only',
        'retrieved_at',
        'file_name',
        'brand_id',
        'market_place_id',
        'raw_data_id',
    ];
}
