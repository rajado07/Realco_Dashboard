<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;

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

    public function aggregatedData()
    {
        // Mengambil semua grup dengan tipe 'shopee_brand_portal_shop'
        $groups = DataGroup::with(['products'])
            ->where('type', 'shopee_brand_portal_shop')
            ->get();

        // Mengambil produk yang tidak memiliki grup
        $ungroupedProducts = ShopeeBrandPortalShopData::whereNull('data_group_id')->get();

        $result = [];

        // Memproses grup dengan produk
        foreach ($groups as $group) {
            $totalGrossSales = $group->products->sum('gross_sales');
            $totalGrossOrders = $group->products->sum('gross_orders');
            $totalGrossUnitsSold = $group->products->sum('gross_units_sold');

            $groupData = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'total_gross_sales' => $totalGrossSales,
                'total_gross_order' => $totalGrossOrders,
                'total_gross_units_sold' => $totalGrossUnitsSold,
                'total_product_views' => $group->products->sum('product_views'),
                'total_product_visitors' => $group->products->sum('product_visitors'),
                'brand_id' => $group->brand_id,
                'average_basket_size' => $totalGrossOrders ? $totalGrossSales / $totalGrossOrders : 0,
                'average_selling_price' => $totalGrossUnitsSold ? $totalGrossSales / $totalGrossUnitsSold : 0,
                'details' => []
            ];

            // Menyimpan produk yang sudah diproses untuk menghindari duplikasi
            $processedProducts = [];

            foreach ($group->products as $product) {
                if (!in_array($product->product_id, $processedProducts)) {
                    $historicalData = $product->where('product_id', $product->product_id)
                        ->orderBy('data_date')
                        ->get(['data_date', 'gross_sales', 'gross_orders', 'gross_units_sold', 'product_views', 'product_visitors'])
                        ->map(function ($data) {
                            return [
                                'data_date' => $data->data_date,
                                'gross_sales' => $data->gross_sales,
                                'gross_orders' => $data->gross_orders,
                                'gross_units_sold' => $data->gross_units_sold,
                                'product_views' => $data->product_views,
                                'product_visitors' => $data->product_visitors,
                                'average_basket_size' => $data->gross_orders ? $data->gross_sales / $data->gross_orders : 0,
                                'average_selling_price' => $data->gross_units_sold ? $data->gross_sales / $data->gross_units_sold : 0,
                            ];
                        });

                    $productDetails = [
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'historical_data' => $historicalData,
                    ];

                    $groupData['details'][] = $productDetails;
                    $processedProducts[] = $product->product_id;
                }
            }

            $result[] = $groupData;
        }

        // Memproses produk yang tidak memiliki grup
        if ($ungroupedProducts->isNotEmpty()) {
            $totalGrossSales = $ungroupedProducts->sum('gross_sales');
            $totalGrossOrders = $ungroupedProducts->sum('gross_orders');
            $totalGrossUnitsSold = $ungroupedProducts->sum('gross_units_sold');

            $unknownGroup = [
                'group_id' => null,
                'group_name' => 'Unknown Group',
                'total_gross_sales' => $totalGrossSales,
                'total_gross_order' => $totalGrossOrders,
                'total_gross_units_sold' => $totalGrossUnitsSold,
                'total_product_views' => $ungroupedProducts->sum('product_views'),
                'total_product_visitors' => $ungroupedProducts->sum('product_visitors'),
                'brand_id' => null,
                'average_basket_size' => $totalGrossOrders ? $totalGrossSales / $totalGrossOrders : 0,
                'average_selling_price' => $totalGrossUnitsSold ? $totalGrossSales / $totalGrossUnitsSold : 0,
                'details' => []
            ];

            $processedProducts = [];

            foreach ($ungroupedProducts as $product) {
                if (!in_array($product->product_id, $processedProducts)) {
                    $historicalData = $product->where('product_id', $product->product_id)
                        ->orderBy('data_date')
                        ->get(['data_date', 'gross_sales', 'gross_orders', 'gross_units_sold', 'product_views', 'product_visitors'])
                        ->map(function ($data) {
                            return [
                                'data_date' => $data->data_date,
                                'gross_sales' => $data->gross_sales,
                                'gross_orders' => $data->gross_orders,
                                'gross_units_sold' => $data->gross_units_sold,
                                'product_views' => $data->product_views,
                                'product_visitors' => $data->product_visitors,
                                'average_basket_size' => $data->gross_orders ? $data->gross_sales / $data->gross_orders : 0,
                                'average_selling_price' => $data->gross_units_sold ? $data->gross_sales / $data->gross_units_sold : 0,
                            ];
                        });

                    $productDetails = [
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'historical_data' => $historicalData,
                    ];

                    $unknownGroup['details'][] = $productDetails;
                    $processedProducts[] = $product->product_id;
                }
            }

            $result[] = $unknownGroup;
        }

        return response()->json($result);
    }

}
