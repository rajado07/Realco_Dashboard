<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

    public function aggregatedData(Request $request)
    {
        // Get the start and end dates from the request, default to the last month
        $startDate = $request->input('start_date', now()->subMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $brandId = $request->input('brand_id');

        // Base query for groups
        $groupQuery = DataGroup::with(['products' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('data_date', [$startDate, $endDate]);
        }])->where('type', 'shopee_brand_portal_shop');

        // Apply brandId filter if present and not zero
        if ($brandId && $brandId != 0) {
            $groupQuery->where('brand_id', $brandId);
        }

        $groups = $groupQuery->get();

        // Base query for ungrouped products
        $ungroupedProductsQuery = ShopeeBrandPortalShopData::whereNull('data_group_id')
            ->whereBetween('data_date', [$startDate, $endDate]);

        // Apply brandId filter if present and not zero
        if ($brandId && $brandId != 0) {
            $ungroupedProductsQuery->where('brand_id', $brandId);
        }

        $ungroupedProducts = $ungroupedProductsQuery->get();

        $result = [];

        // Process groups with products
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

            // Store processed products to avoid duplication
            $processedProducts = [];

            foreach ($group->products as $product) {
                if (!in_array($product->product_id, $processedProducts)) {
                    $historicalData = ShopeeBrandPortalShopData::where('product_id', $product->product_id)
                        ->whereBetween('data_date', [$startDate, $endDate])
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

        // Process ungrouped products
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
                    $historicalData = ShopeeBrandPortalShopData::where('product_id', $product->product_id)
                        ->whereBetween('data_date', [$startDate, $endDate])
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

    public function getSummary(Request $request)
    {
        // Get the start and end dates from the request, default to the last month
        $startDate = Carbon::parse($request->input('start_date', now()->subMonth()->toDateString()));
        $endDate = Carbon::parse($request->input('end_date', now()->toDateString()));
        $brandId = $request->input('brand_id');  // Capture brand_id input

        // Calculate the interval between start and end dates
        $interval = $startDate->diffInDays($endDate) + 1;

        // Calculate the previous date range
        $previousStartDate = $startDate->copy()->subDays($interval);
        $previousEndDate = $endDate->copy()->subDays($interval);

        // Query builder initialization
        $query = ShopeeBrandPortalShopData::whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        // Conditionally add brand_id to the query if provided and not zero
        if (!empty($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        // Get all relevant data within the date range
        $data = $query->get();

        // Calculate the total sums for gross_sales, gross_orders, and gross_units_sold
        $totalGrossSales = $data->sum('gross_sales');
        $totalGrossOrders = $data->sum('gross_orders');
        $totalGrossUnitsSold = $data->sum('gross_units_sold');

        // Calculate average basket size and average selling price
        $averageBasketSize = $totalGrossOrders ? $totalGrossSales / $totalGrossOrders : 0;
        $averageSellingPrice = $totalGrossUnitsSold ? $totalGrossSales / $totalGrossUnitsSold : 0;

        // Query builder for the previous date range
        $previousQuery = ShopeeBrandPortalShopData::whereBetween('data_date', [$previousStartDate->toDateString(), $previousEndDate->toDateString()]);

        // Conditionally add brand_id to the previous query if provided and not zero
        if (!empty($brandId) && $brandId != 0) {
            $previousQuery->where('brand_id', $brandId);
        }

        // Get all relevant data within the previous date range
        $previousData = $previousQuery->get();

        // Calculate the total sums for gross_sales, gross_orders, and gross_units_sold for the previous date range
        $previousTotalGrossSales = $previousData->sum('gross_sales');
        $previousTotalGrossOrders = $previousData->sum('gross_orders');
        $previousTotalGrossUnitsSold = $previousData->sum('gross_units_sold');

        // Calculate average basket size and average selling price for the previous date range
        $previousAverageBasketSize = $previousTotalGrossOrders ? $previousTotalGrossSales / $previousTotalGrossOrders : 0;
        $previousAverageSellingPrice = $previousTotalGrossUnitsSold ? $previousTotalGrossSales / $previousTotalGrossUnitsSold : 0;

        // Calculate the percentage changes
        $grossSalesChange = $previousTotalGrossSales ? (($totalGrossSales - $previousTotalGrossSales) / $previousTotalGrossSales) * 100 : 0;
        $grossOrdersChange = $previousTotalGrossOrders ? (($totalGrossOrders - $previousTotalGrossOrders) / $previousTotalGrossOrders) * 100 : 0;
        $grossUnitsSoldChange = $previousTotalGrossUnitsSold ? (($totalGrossUnitsSold - $previousTotalGrossUnitsSold) / $previousTotalGrossUnitsSold) * 100 : 0;
        $averageBasketSizeChange = $previousAverageBasketSize ? (($averageBasketSize - $previousAverageBasketSize) / $previousAverageBasketSize) * 100 : 0;
        $averageSellingPriceChange = $previousAverageSellingPrice ? (($averageSellingPrice - $previousAverageSellingPrice) / $previousAverageSellingPrice) * 100 : 0;

        // Create the summary array
        $summary = [
            'total_gross_sales' => $totalGrossSales,
            'total_gross_orders' => $totalGrossOrders,
            'total_gross_units_sold' => $totalGrossUnitsSold,
            'average_basket_size' => $averageBasketSize,
            'average_selling_price' => $averageSellingPrice,
            'gross_sales_change_percentage' => $grossSalesChange,
            'gross_orders_change_percentage' => $grossOrdersChange,
            'gross_units_sold_change_percentage' => $grossUnitsSoldChange,
            'average_basket_size_change_percentage' => $averageBasketSizeChange,
            'average_selling_price_change_percentage' => $averageSellingPriceChange,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'brand_id' => $brandId,  // Optionally include brand_id in the response for clarity
        ];

        // Return the summary as a JSON response
        return response()->json($summary);
    }


    public function latestRetrievedAt()
    {
        // Retrieve the latest data based on the 'retrieved_at' column
        $latestData = ShopeeBrandPortalShopData::orderBy('retrieved_at', 'desc')->first();

        // Return the latest retrieved_at timestamp, or a default value if no data exists
        return $latestData ? $latestData->retrieved_at : 'No data available';
    }
}
