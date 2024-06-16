<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopeeBrandPortalShopData;

class ShopeeBrandPortalShopDataController extends Controller
{
    public function index()
    {
        // Specify the columns you want to retrieve
        $data = ShopeeBrandPortalShopData::select([
            'id',
            'product_name',
            'product_id',
            'gross_sales',
            'gross_orders',
            'gross_units_sold',
            'product_views',
            'product_visitors',
            'data_date'
        ])->get();

        return response()->json($data);
    }
}
