<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;

class ShopeeBrandPortalShopDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        // Retrieve the data for the current period
        $query = ShopeeBrandPortalShopData::with('dataGroup')
            ->select([
                'id',
                'product_name',
                'product_id',
                'gross_sales',
                'gross_orders',
                'gross_units_sold',
                'product_views',
                'product_visitors',
                'data_date',
                'data_group_id',
                'brand_id'
            ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $results = $query->get();

        // Retrieve the data for the previous period
        $previousStartDate = Carbon::parse($startDate)->subMonth()->toDateString();
        $previousEndDate = Carbon::parse($endDate)->subMonth()->toDateString();

        $previousQuery = ShopeeBrandPortalShopData::with('dataGroup')
            ->select([
                'data_group_id',
                'gross_sales',
                'gross_orders',
                'gross_units_sold',
                'product_views',
                'product_visitors',
            ])
            ->whereBetween('data_date', [$previousStartDate, $previousEndDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $previousQuery->where('brand_id', $brandId);
        }

        $previousResults = $previousQuery->get();

        // Group by 'data_group_id' and prepare the nested structure
        $groupedResults = $results->groupBy('data_group_id')->map(function ($items, $groupId) use ($previousResults, $startDate, $endDate) {
            // Aggregate data for the current period
            $currentTotals = [
                'gross_sales' => $items->sum('gross_sales'),
                'gross_orders' => $items->sum('gross_orders'),
                'gross_units_sold' => $items->sum('gross_units_sold'),
                'product_views' => $items->sum('product_views'),
                'product_visitors' => $items->sum('product_visitors'),
                'average_basket_size' => $items->sum('gross_orders') ? $items->sum('gross_sales') / $items->sum('gross_orders') : 0,
                'average_selling_price' => $items->sum('gross_units_sold') ? $items->sum('gross_sales') / $items->sum('gross_units_sold') : 0,
                'conversion' => $items->sum('product_views') ? ($items->sum('gross_units_sold') / $items->sum('product_views')) * 100 : 0,
            ];

            // Retrieve previous totals for the same group
            $previousItems = $previousResults->where('data_group_id', $groupId);
            $previousTotals = [
                'gross_sales' => $previousItems->sum('gross_sales'),
                'gross_orders' => $previousItems->sum('gross_orders'),
                'gross_units_sold' => $previousItems->sum('gross_units_sold'),
                'product_views' => $previousItems->sum('product_views'),
                'product_visitors' => $previousItems->sum('product_visitors'),
                'average_basket_size' => $previousItems->sum('gross_orders') ? $previousItems->sum('gross_sales') / $previousItems->sum('gross_orders') : 0,
                'average_selling_price' => $previousItems->sum('gross_units_sold') ? $previousItems->sum('gross_sales') / $previousItems->sum('gross_units_sold') : 0,
                'conversion' => $previousItems->sum('product_views') ? ($previousItems->sum('gross_units_sold') / $previousItems->sum('product_views')) * 100 : 0,
            ];

            $changes = [];
            foreach ($currentTotals as $key => $currentValue) {
                $previousValue = $previousTotals[$key];
                $changes[$key] = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;
            }

            // Format conversion and changes as percentages
            $currentTotals['conversion'] = number_format($currentTotals['conversion'], 2) . '%';
            $previousTotals['conversion'] = number_format($previousTotals['conversion'], 2) . '%';
            $changes['conversion'] = number_format($changes['conversion'], 2) . '%';

            // Prepare group data with totals and changes
            $groupData = [
                'group_id' => $groupId,
                'group_name' => $items->first()->dataGroup->name ?? 'Unknown Group',
                'gross_sales' => [
                    'now' => $currentTotals['gross_sales'],
                    'previous' => $previousTotals['gross_sales'],
                    'change' => $changes['gross_sales'],
                ],
                'gross_orders' => [
                    'now' => $currentTotals['gross_orders'],
                    'previous' => $previousTotals['gross_orders'],
                    'change' => $changes['gross_orders'],
                ],
                'gross_units_sold' => [
                    'now' => $currentTotals['gross_units_sold'],
                    'previous' => $previousTotals['gross_units_sold'],
                    'change' => $changes['gross_units_sold'],
                ],
                'product_views' => [
                    'now' => $currentTotals['product_views'],
                    'previous' => $previousTotals['product_views'],
                    'change' => $changes['product_views'],
                ],
                'product_visitors' => [
                    'now' => $currentTotals['product_visitors'],
                    'previous' => $previousTotals['product_visitors'],
                    'change' => $changes['product_visitors'],
                ],
                'average_basket_size' => [
                    'now' => $currentTotals['average_basket_size'],
                    'previous' => $previousTotals['average_basket_size'],
                    'change' => $changes['average_basket_size'],
                ],
                'average_selling_price' => [
                    'now' => $currentTotals['average_selling_price'],
                    'previous' => $previousTotals['average_selling_price'],
                    'change' => $changes['average_selling_price'],
                ],
                'conversion' => [
                    'now' => $currentTotals['conversion'],
                    'previous' => $previousTotals['conversion'],
                    'change' => $changes['conversion'],
                ],
                'details' => []
            ];

            // Collect products in the group
            $processedProducts = [];
            foreach ($items->groupBy('product_id') as $productId => $productItems) {
                if (!in_array($productId, $processedProducts)) {
                    // Retrieve historical data for each product
                    $historicalData = ShopeeBrandPortalShopData::where('product_id', $productId)
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
                                'conversion' => $data->product_views ? number_format(($data->gross_units_sold / $data->product_views) * 100, 2) . '%' : '0%',
                            ];
                        });

                    // Prepare product details with historical data
                    $productDetails = [
                        'product_id' => $productId,
                        'product_name' => $productItems->first()->product_name,
                        'historical_data' => $historicalData,
                    ];

                    $groupData['details'][] = $productDetails;
                    $processedProducts[] = $productId;
                }
            }

            return $groupData;
        })->values(); // Use values() to reset the keys

        return response()->json($groupedResults);
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
