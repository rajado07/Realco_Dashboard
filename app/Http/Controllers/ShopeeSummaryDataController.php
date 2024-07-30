<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\ShopeeBrandPortalAdsData;
use App\Models\ShopeeSellerCenterLiveStreamingData;
use App\Models\MetaCpasData;
use App\Models\DataGroup;


class ShopeeSummaryDataController extends Controller
{
    public function shopeeBrand(Request $request)
    {
        // Set the date range for this week and previous week
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $previousWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        // Fetch data
        $data = ShopeeBrandPortalShopData::whereBetween('data_date', [$previousWeekStart, $thisWeekEnd])->get();

        // Group data by data_group_id
        $groupedData = $data->groupBy('data_group_id');

        $result = [];

        foreach ($groupedData as $groupId => $items) {
            if ($groupId === null) {
                $groupName = 'Unknown Group';
            } else {
                $group = DataGroup::find($groupId);
                $groupName = $group ? $group->name : 'Unknown Group';
            }

            // Calculate totals for this week
            $thisWeekItems = $items->filter(function ($item) use ($thisWeekStart, $thisWeekEnd) {
                return Carbon::parse($item->data_date)->between($thisWeekStart, $thisWeekEnd);
            });
            $thisWeekProductViews = $thisWeekItems->sum('product_views');
            $thisWeekGrossUnitsSold = $thisWeekItems->sum('gross_units_sold');
            $thisWeekGrossSales = $thisWeekItems->sum('gross_sales');

            // Calculate totals for previous week
            $previousWeekItems = $items->filter(function ($item) use ($previousWeekStart, $previousWeekEnd) {
                return Carbon::parse($item->data_date)->between($previousWeekStart, $previousWeekEnd);
            });
            $previousWeekProductViews = $previousWeekItems->sum('product_views');
            $previousWeekGrossUnitsSold = $previousWeekItems->sum('gross_units_sold');
            $previousWeekGrossSales = $previousWeekItems->sum('gross_sales');

            // Calculate growth
            $productViewsGrowth = $previousWeekProductViews > 0 ? (($thisWeekProductViews - $previousWeekProductViews) / $previousWeekProductViews) * 100 : 0;

            if ($thisWeekProductViews > 0 && $previousWeekProductViews > 0 && $previousWeekGrossUnitsSold > 0) {
                $conversionGrowth = ((($thisWeekGrossUnitsSold / $thisWeekProductViews) - ($previousWeekGrossUnitsSold / $previousWeekProductViews)) / ($previousWeekGrossUnitsSold / $previousWeekProductViews)) * 100;
            } else {
                $conversionGrowth = 0;
            }

            $gmvGrowth = $previousWeekGrossSales > 0 ? (($thisWeekGrossSales - $previousWeekGrossSales) / $previousWeekGrossSales) * 100 : 0;

            // Prepare the result
            $result[] = [
                'data_group_name' => $groupName,
                'product_views' => [
                    'this_week' => $thisWeekProductViews,
                    'previous_week' => $previousWeekProductViews,
                    'growth' => number_format($productViewsGrowth, 2) . '%'
                ],
                'conversion' => [
                    'this_week' => $thisWeekProductViews > 0 ? number_format($thisWeekGrossUnitsSold / $thisWeekProductViews * 100, 2) . '%' : '0%',
                    'previous_week' => $previousWeekProductViews > 0 ? number_format($previousWeekGrossUnitsSold / $previousWeekProductViews * 100, 2) . '%' : '0%',
                    'growth' => number_format($conversionGrowth, 2) . '%'
                ],
                'GMV' => [
                    'this_week' => $thisWeekGrossSales,
                    'previous_week' => $previousWeekGrossSales,
                    'growth' => number_format($gmvGrowth, 2) . '%'
                ]
            ];
        }

        return response()->json($result);
    }

    public function metaCpas(Request $request)
    {
        // Set the date range for this week and previous week
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $previousWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $previousWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        // Fetch data
        $data = MetaCpasData::whereBetween('data_date', [$previousWeekStart, $thisWeekEnd])->get();

        // Group data by data_group_id
        $groupedData = $data->groupBy('data_group_id');

        $result = [];

        foreach ($groupedData as $groupId => $items) {
            if ($groupId === null) {
                $groupName = 'Unknown Group';
            } else {
                $group = DataGroup::find($groupId);
                $groupName = $group ? $group->name : 'Unknown Group';
            }

            // Calculate totals for this week
            $thisWeekItems = $items->filter(function ($item) use ($thisWeekStart, $thisWeekEnd) {
                return Carbon::parse($item->data_date)->between($thisWeekStart, $thisWeekEnd);
            });
            $thisWeekAmountSpent = $thisWeekItems->sum('amount_spent');
            $thisWeekPurchasesConversionValue = $thisWeekItems->sum('purchases_conversion_value_for_shared_items_only');

            // Calculate totals for previous week
            $previousWeekItems = $items->filter(function ($item) use ($previousWeekStart, $previousWeekEnd) {
                return Carbon::parse($item->data_date)->between($previousWeekStart, $previousWeekEnd);
            });
            $previousWeekAmountSpent = $previousWeekItems->sum('amount_spent');
            $previousWeekPurchasesConversionValue = $previousWeekItems->sum('purchases_conversion_value_for_shared_items_only');

            // Calculate growth
            $amountSpentGrowth = $previousWeekAmountSpent > 0 ? (($thisWeekAmountSpent - $previousWeekAmountSpent) / $previousWeekAmountSpent) * 100 : 0;
            $purchasesConversionValueGrowth = $previousWeekPurchasesConversionValue > 0 ? (($thisWeekPurchasesConversionValue - $previousWeekPurchasesConversionValue) / $previousWeekPurchasesConversionValue) * 100 : 0;

            // Calculate ROAS
            $thisWeekROAS = $thisWeekAmountSpent > 0 ? $thisWeekPurchasesConversionValue / $thisWeekAmountSpent : 0;
            $previousWeekROAS = $previousWeekAmountSpent > 0 ? $previousWeekPurchasesConversionValue / $previousWeekAmountSpent : 0;
            $roasGrowth = $previousWeekROAS > 0 ? (($thisWeekROAS - $previousWeekROAS) / $previousWeekROAS) * 100 : 0;

            // Prepare the result
            $result[] = [
                'data_group_name' => $groupName,
                'amount_spent' => [
                    'this_week' => $thisWeekAmountSpent,
                    'previous_week' => $previousWeekAmountSpent,
                    'growth' => number_format($amountSpentGrowth, 2) . '%'
                ],
                'purchases_conversion_value' => [
                    'this_week' => $thisWeekPurchasesConversionValue,
                    'previous_week' => $previousWeekPurchasesConversionValue,
                    'growth' => number_format($purchasesConversionValueGrowth, 2) . '%'
                ],
                'roas' => [
                    'this_week' => number_format($thisWeekROAS, 2),
                    'previous_week' => number_format($previousWeekROAS, 2),
                    'growth' => number_format($roasGrowth, 2) . '%'
                ]
            ];
        }

        return response()->json($result);
    }

    public function shopeeAds(Request $request)
    {
        // Set the date range for this week (last 7 days) and previous week (7 days before the last 7 days)
        $thisWeekEnd = Carbon::now();
        $thisWeekStart = Carbon::now()->subDays(6); // 7 days including today
        $previousWeekEnd = Carbon::now()->subDays(7);
        $previousWeekStart = Carbon::now()->subDays(13); // 7 days before the last 7 days

        // Fetch data for the specified date range
        $data = ShopeeBrandPortalAdsData::whereBetween('data_date', [$previousWeekStart, $thisWeekEnd])->get();

        // Calculate totals for this week
        $thisWeekItems = $data->filter(function ($item) use ($thisWeekStart, $thisWeekEnd) {
            return Carbon::parse($item->data_date)->between($thisWeekStart, $thisWeekEnd);
        });
        $thisWeekAdsSpent = $thisWeekItems->sum('ads_spent');
        $thisWeekGrossSales = $thisWeekItems->sum('gross_sales');

        // Calculate totals for previous week
        $previousWeekItems = $data->filter(function ($item) use ($previousWeekStart, $previousWeekEnd) {
            return Carbon::parse($item->data_date)->between($previousWeekStart, $previousWeekEnd);
        });
        $previousWeekAdsSpent = $previousWeekItems->sum('ads_spent');
        $previousWeekGrossSales = $previousWeekItems->sum('gross_sales');

        // Calculate ROAS
        $thisWeekROAS = $thisWeekAdsSpent > 0 ? $thisWeekGrossSales / $thisWeekAdsSpent : 0;
        $previousWeekROAS = $previousWeekAdsSpent > 0 ? $previousWeekGrossSales / $previousWeekAdsSpent : 0;
        $roasGrowth = $previousWeekROAS > 0 ? (($thisWeekROAS - $previousWeekROAS) / $previousWeekROAS) * 100 : 0;

        // Prepare the result
        $result = [
            'ads_spent' => [
                'this_week' => $thisWeekAdsSpent,
                'previous_week' => $previousWeekAdsSpent,
                'growth' => number_format($thisWeekAdsSpent > 0 && $previousWeekAdsSpent > 0 ? (($thisWeekAdsSpent - $previousWeekAdsSpent) / $previousWeekAdsSpent) * 100 : 0, 2) . '%'
            ],
            'gross_sales' => [
                'this_week' => $thisWeekGrossSales,
                'previous_week' => $previousWeekGrossSales,
                'growth' => number_format($thisWeekGrossSales > 0 && $previousWeekGrossSales > 0 ? (($thisWeekGrossSales - $previousWeekGrossSales) / $previousWeekGrossSales) * 100 : 0, 2) . '%'
            ],
            'roas' => [
                'this_week' => number_format($thisWeekROAS, 2),
                'previous_week' => number_format($previousWeekROAS, 2),
                'growth' => number_format($roasGrowth, 2) . '%'
            ]
        ];

        return response()->json($result);
    }


    public function shopeeLiveStream(Request $request)
    {
        // Set the date range for May and June
        $mayStart = Carbon::create(null, 5, 1)->startOfDay();
        $mayEnd = Carbon::create(null, 5, 31)->endOfDay();
        $juneStart = Carbon::create(null, 6, 1)->startOfDay();
        $juneEnd = Carbon::create(null, 6, 30)->endOfDay();

        // Fetch data for May and June
        $data = ShopeeSellerCenterLiveStreamingData::whereBetween('data_date', [$mayStart, $juneEnd])->get();

        // Calculate totals for May
        $mayItems = $data->filter(function ($item) use ($mayStart, $mayEnd) {
            return Carbon::parse($item->data_date)->between($mayStart, $mayEnd);
        });
        $mayTotalSales = $mayItems->sum('sales');
        $mayTotalDurationInSeconds = $mayItems->sum('duration');
        $mayTotalDurationInHours = $mayTotalDurationInSeconds / 3600;
        $mayGMVPerHour = $mayTotalDurationInHours > 0 ? $mayTotalSales / $mayTotalDurationInHours : 0;

        // Calculate totals for June
        $juneItems = $data->filter(function ($item) use ($juneStart, $juneEnd) {
            return Carbon::parse($item->data_date)->between($juneStart, $juneEnd);
        });
        $juneTotalSales = $juneItems->sum('sales');
        $juneTotalDurationInSeconds = $juneItems->sum('duration');
        $juneTotalDurationInHours = $juneTotalDurationInSeconds / 3600;
        $juneGMVPerHour = $juneTotalDurationInHours > 0 ? $juneTotalSales / $juneTotalDurationInHours : 0;

        // Convert total duration to hours and minutes for May
        $mayHours = floor($mayTotalDurationInSeconds / 3600);
        $mayMinutes = floor(($mayTotalDurationInSeconds % 3600) / 60);

        // Convert total duration to hours and minutes for June
        $juneHours = floor($juneTotalDurationInSeconds / 3600);
        $juneMinutes = floor(($juneTotalDurationInSeconds % 3600) / 60);

        // Calculate growth
        $salesGrowth = $mayTotalSales > 0 ? (($juneTotalSales - $mayTotalSales) / $mayTotalSales) * 100 : 0;
        $gmvPerHourGrowth = $mayGMVPerHour > 0 ? (($juneGMVPerHour - $mayGMVPerHour) / $mayGMVPerHour) * 100 : 0;
        $durationGrowth = $mayTotalDurationInSeconds > 0 ? (($juneTotalDurationInSeconds - $mayTotalDurationInSeconds) / $mayTotalDurationInSeconds) * 100 : 0;

        // Prepare the result
        $result = [
            'total_sales' => [
                'may' => $mayTotalSales,
                'june' => $juneTotalSales,
                'growth' => number_format($salesGrowth, 2) . '%'
            ],
            'total_duration' => [
                'may' => sprintf('%d hours %d minutes', $mayHours, $mayMinutes),
                'june' => sprintf('%d hours %d minutes', $juneHours, $juneMinutes),
                'growth' => number_format($durationGrowth, 2) . '%'
            ],
            'gmv_per_hour' => [
                'may' => number_format($mayGMVPerHour, 2),
                'june' => number_format($juneGMVPerHour, 2),
                'growth' => number_format($gmvPerHourGrowth, 2) . '%'
            ]
        ];

        return response()->json($result);
    }
}
