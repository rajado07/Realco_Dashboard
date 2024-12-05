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

    private function getDateRanges(Request $request)
    {
        // Set default date ranges
        $firstPeriodStart = Carbon::now()->startOfWeek();
        $firstPeriodEnd = Carbon::now()->endOfWeek();
        $secondPeriodStart = Carbon::now()->subWeek()->startOfWeek();
        $secondPeriodEnd = Carbon::now()->subWeek()->endOfWeek();

        // Check for custom date range requests
        $startDate1 = $request->input('startDate1');
        $endDate1 = $request->input('endDate1');
        $startDate2 = $request->input('startDate2');
        $endDate2 = $request->input('endDate2');

        if ($startDate1 && $endDate1) {
            // Set custom date range for comparison
            $firstPeriodStart = Carbon::parse($startDate1);
            $firstPeriodEnd = Carbon::parse($endDate1);

            if ($startDate2 && $endDate2) {
                // If startDate2 and endDate2 are provided, use them for the comparison range
                $secondPeriodStart = Carbon::parse($startDate2);
                $secondPeriodEnd = Carbon::parse($endDate2);
            } else {
                // If only startDate1 and endDate1 are provided, set previous range to the same duration before startDate1
                $diffInDays = $firstPeriodStart->diffInDays($firstPeriodEnd);
                $secondPeriodStart = $firstPeriodStart->copy()->subDays($diffInDays + 1);
                $secondPeriodEnd = $secondPeriodStart->copy()->addDays($diffInDays);
            }
        }

        return [$firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd];
    }

    public function shopeeBrand(Request $request)
    {
        // Get date ranges
        list($firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd) = $this->getDateRanges($request);

        // Fetch data
        $data = ShopeeBrandPortalShopData::whereBetween('data_date', [$secondPeriodStart, $firstPeriodEnd]);

        // Filter by brandId if provided
        $brandId = $request->input('brandId', 0);
        if ($brandId != 0) {
            $data = $data->where('brand_id', $brandId);
        }

        $data = $data->get();

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

            // Calculate totals for the first period
            $firstPeriodItems = $items->filter(function ($item) use ($firstPeriodStart, $firstPeriodEnd) {
                return Carbon::parse($item->data_date)->between($firstPeriodStart, $firstPeriodEnd);
            });
            $firstPeriodProductViews = $firstPeriodItems->sum('product_views');
            $firstPeriodGrossUnitsSold = $firstPeriodItems->sum('gross_units_sold');
            $firstPeriodGrossSales = $firstPeriodItems->sum('gross_sales');

            // Calculate totals for the second period
            $secondPeriodItems = $items->filter(function ($item) use ($secondPeriodStart, $secondPeriodEnd) {
                return Carbon::parse($item->data_date)->between($secondPeriodStart, $secondPeriodEnd);
            });
            $secondPeriodProductViews = $secondPeriodItems->sum('product_views');
            $secondPeriodGrossUnitsSold = $secondPeriodItems->sum('gross_units_sold');
            $secondPeriodGrossSales = $secondPeriodItems->sum('gross_sales');

            // Calculate growth
            $productViewsGrowth = $secondPeriodProductViews > 0 ? (($firstPeriodProductViews - $secondPeriodProductViews) / $secondPeriodProductViews) * 100 : 0;

            if ($firstPeriodProductViews > 0 && $secondPeriodProductViews > 0 && $secondPeriodGrossUnitsSold > 0) {
                $conversionGrowth = ((($firstPeriodGrossUnitsSold / $firstPeriodProductViews) - ($secondPeriodGrossUnitsSold / $secondPeriodProductViews)) / ($secondPeriodGrossUnitsSold / $secondPeriodProductViews)) * 100;
            } else {
                $conversionGrowth = 0;
            }

            $gmvGrowth = $secondPeriodGrossSales > 0 ? (($firstPeriodGrossSales - $secondPeriodGrossSales) / $secondPeriodGrossSales) * 100 : 0;

            // Prepare the result
            $result[] = [
                'data_group_name' => $groupName,
                'product_views' => [
                    'first_period' => $firstPeriodProductViews,
                    'second_period' => $secondPeriodProductViews,
                    'growth' => number_format($productViewsGrowth, 2) . '%'
                ],
                'conversion' => [
                    'first_period' => $firstPeriodProductViews > 0 ? number_format($firstPeriodGrossUnitsSold / $firstPeriodProductViews * 100, 2) . '%' : '0%',
                    'second_period' => $secondPeriodProductViews > 0 ? number_format($secondPeriodGrossUnitsSold / $secondPeriodProductViews * 100, 2) . '%' : '0%',
                    'growth' => number_format($conversionGrowth, 2) . '%'
                ],
                'gmv' => [
                    'first_period' => $firstPeriodGrossSales,
                    'second_period' => $secondPeriodGrossSales,
                    'growth' => number_format($gmvGrowth, 2) . '%'
                ]
            ];
        }

        return response()->json($result);
    }


    public function metaCpas(Request $request)
    {
        // Get date ranges
        list($firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd) = $this->getDateRanges($request);

        // Fetch data with filters
        $dataQuery = MetaCpasData::where('market_place_id', 1)
            ->whereBetween('data_date', [$secondPeriodStart, $firstPeriodEnd]);

        // Filter by brand_id if provided
        $brandId = $request->input('brandId', 0);
        if ($brandId != 0) {
            $dataQuery = $dataQuery->where('brand_id', $brandId);
        }

        $data = $dataQuery->get();

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

            // Calculate totals for the first period
            $firstPeriodItems = $items->filter(function ($item) use ($firstPeriodStart, $firstPeriodEnd) {
                return Carbon::parse($item->data_date)->between($firstPeriodStart, $firstPeriodEnd);
            });
            $firstPeriodAmountSpent = $firstPeriodItems->sum('amount_spent');
            $firstPeriodPurchasesConversionValue = $firstPeriodItems->sum('purchases_conversion_value_for_shared_items_only');

            // Calculate totals for the second period
            $secondPeriodItems = $items->filter(function ($item) use ($secondPeriodStart, $secondPeriodEnd) {
                return Carbon::parse($item->data_date)->between($secondPeriodStart, $secondPeriodEnd);
            });
            $secondPeriodAmountSpent = $secondPeriodItems->sum('amount_spent');
            $secondPeriodPurchasesConversionValue = $secondPeriodItems->sum('purchases_conversion_value_for_shared_items_only');

            // Calculate growth
            $amountSpentGrowth = $secondPeriodAmountSpent > 0 ? (($firstPeriodAmountSpent - $secondPeriodAmountSpent) / $secondPeriodAmountSpent) * 100 : 0;
            $purchasesConversionValueGrowth = $secondPeriodPurchasesConversionValue > 0 ? (($firstPeriodPurchasesConversionValue - $secondPeriodPurchasesConversionValue) / $secondPeriodPurchasesConversionValue) * 100 : 0;

            // Calculate ROAS
            $firstPeriodROAS = $firstPeriodAmountSpent > 0 ? $firstPeriodPurchasesConversionValue / $firstPeriodAmountSpent : 0;
            $secondPeriodROAS = $secondPeriodAmountSpent > 0 ? $secondPeriodPurchasesConversionValue / $secondPeriodAmountSpent : 0;
            $roasGrowth = $secondPeriodROAS > 0 ? (($firstPeriodROAS - $secondPeriodROAS) / $secondPeriodROAS) * 100 : 0;

            // Prepare the result
            $result[] = [
                'data_group_name' => $groupName,
                'amount_spent' => [
                    'first_period' => $firstPeriodAmountSpent,
                    'second_period' => $secondPeriodAmountSpent,
                    'growth' => number_format($amountSpentGrowth, 2) . '%'
                ],
                'purchases_conversion_value' => [
                    'first_period' => $firstPeriodPurchasesConversionValue,
                    'second_period' => $secondPeriodPurchasesConversionValue,
                    'growth' => number_format($purchasesConversionValueGrowth, 2) . '%'
                ],
                'roas' => [
                    'first_period' => number_format($firstPeriodROAS, 2),
                    'second_period' => number_format($secondPeriodROAS, 2),
                    'growth' => number_format($roasGrowth, 2) . '%'
                ]
            ];
        }

        return response()->json($result);
    }

    public function shopeeAds(Request $request)
    {
        // Ambil range tanggal berdasarkan request atau default
        [$firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd] = $this->getDateRanges($request);

        // Ambil data dari database berdasarkan periode yang diberikan
        $data = ShopeeBrandPortalAdsData::whereBetween('data_date', [$secondPeriodStart, $firstPeriodEnd]);

        // Filter berdasarkan brandId jika disediakan
        $brandId = $request->input('brandId', 0);
        if ($brandId != 0) {
            $data = $data->where('brand_id', $brandId);
        }

        // Dapatkan data dari database
        $data = $data->get();

        // Perhitungan untuk first period
        $firstPeriodItems = $data->filter(function ($item) use ($firstPeriodStart, $firstPeriodEnd) {
            return Carbon::parse($item->data_date)->between($firstPeriodStart, $firstPeriodEnd);
        });
        $firstPeriodAdsSpend = $firstPeriodItems->sum('ads_spend');
        $firstPeriodGrossSales = $firstPeriodItems->sum('gross_sales');

        // Perhitungan untuk second period
        $secondPeriodItems = $data->filter(function ($item) use ($secondPeriodStart, $secondPeriodEnd) {
            return Carbon::parse($item->data_date)->between($secondPeriodStart, $secondPeriodEnd);
        });
        $secondPeriodAdsSpend = $secondPeriodItems->sum('ads_spend');
        $secondPeriodGrossSales = $secondPeriodItems->sum('gross_sales');

        // Hitung ROAS
        $firstPeriodROAS = $firstPeriodAdsSpend > 0 ? $firstPeriodGrossSales / $firstPeriodAdsSpend : 0;
        $secondPeriodROAS = $secondPeriodAdsSpend > 0 ? $secondPeriodGrossSales / $secondPeriodAdsSpend : 0;
        $roasGrowth = $secondPeriodROAS > 0 ? (($firstPeriodROAS - $secondPeriodROAS) / $secondPeriodROAS) * 100 : 0;

        // Siapkan hasil untuk response
        $result = [
            'ads_spend' => [
                'first_period' => $firstPeriodAdsSpend,
                'second_period' => $secondPeriodAdsSpend,
                'growth' => number_format($firstPeriodAdsSpend > 0 && $secondPeriodAdsSpend > 0 ? (($firstPeriodAdsSpend - $secondPeriodAdsSpend) / $secondPeriodAdsSpend) * 100 : 0, 2) . '%'
            ],
            'gross_sales' => [
                'first_period' => $firstPeriodGrossSales,
                'second_period' => $secondPeriodGrossSales,
                'growth' => number_format($firstPeriodGrossSales > 0 && $secondPeriodGrossSales > 0 ? (($firstPeriodGrossSales - $secondPeriodGrossSales) / $secondPeriodGrossSales) * 100 : 0, 2) . '%'
            ],
            'roas' => [
                'first_period' => number_format($firstPeriodROAS, 2),
                'second_period' => number_format($secondPeriodROAS, 2),
                'growth' => number_format($roasGrowth, 2) . '%'
            ]
        ];

        return response()->json($result);
    }


    public function shopeeLiveStream(Request $request)
    {
        // Ambil range tanggal berdasarkan request atau default
        [$firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd] = $this->getDateRanges($request);

        // Ambil data dari database berdasarkan periode yang diberikan
        $data = ShopeeSellerCenterLiveStreamingData::whereBetween('data_date', [$secondPeriodStart, $firstPeriodEnd]);

        // Filter berdasarkan brandId jika disediakan
        $brandId = $request->input('brandId', 0);
        if ($brandId != 0) {
            $data = $data->where('brand_id', $brandId);
        }

        // Dapatkan data dari database
        $data = $data->get();

        // Perhitungan untuk first period
        $firstPeriodItems = $data->filter(function ($item) use ($firstPeriodStart, $firstPeriodEnd) {
            return Carbon::parse($item->data_date)->between($firstPeriodStart, $firstPeriodEnd);
        });
        $firstPeriodTotalSales = $firstPeriodItems->sum('sales');
        $firstPeriodTotalDurationInSeconds = $firstPeriodItems->sum('duration');
        $firstPeriodTotalDurationInHours = $firstPeriodTotalDurationInSeconds / 3600;
        $firstPeriodGMVPerHour = $firstPeriodTotalDurationInHours > 0 ? $firstPeriodTotalSales / $firstPeriodTotalDurationInHours : 0;

        // Perhitungan untuk second period
        $secondPeriodItems = $data->filter(function ($item) use ($secondPeriodStart, $secondPeriodEnd) {
            return Carbon::parse($item->data_date)->between($secondPeriodStart, $secondPeriodEnd);
        });
        $secondPeriodTotalSales = $secondPeriodItems->sum('sales');
        $secondPeriodTotalDurationInSeconds = $secondPeriodItems->sum('duration');
        $secondPeriodTotalDurationInHours = $secondPeriodTotalDurationInSeconds / 3600;
        $secondPeriodGMVPerHour = $secondPeriodTotalDurationInHours > 0 ? $secondPeriodTotalSales / $secondPeriodTotalDurationInHours : 0;

        // Konversi durasi total ke jam dan menit untuk first period
        $firstPeriodHours = floor($firstPeriodTotalDurationInSeconds / 3600);
        $firstPeriodMinutes = floor(($firstPeriodTotalDurationInSeconds % 3600) / 60);

        // Konversi durasi total ke jam dan menit untuk second period
        $secondPeriodHours = floor($secondPeriodTotalDurationInSeconds / 3600);
        $secondPeriodMinutes = floor(($secondPeriodTotalDurationInSeconds % 3600) / 60);

        // Hitung pertumbuhan
        $salesGrowth = $firstPeriodTotalSales > 0 ? (($secondPeriodTotalSales - $firstPeriodTotalSales) / $firstPeriodTotalSales) * 100 : 0;
        $gmvPerHourGrowth = $firstPeriodGMVPerHour > 0 ? (($secondPeriodGMVPerHour - $firstPeriodGMVPerHour) / $firstPeriodGMVPerHour) * 100 : 0;
        $durationGrowth = $firstPeriodTotalDurationInSeconds > 0 ? (($secondPeriodTotalDurationInSeconds - $firstPeriodTotalDurationInSeconds) / $firstPeriodTotalDurationInSeconds) * 100 : 0;

        // Siapkan hasil untuk response
        $result = [
            'total_sales' => [
                'first_period' => $firstPeriodTotalSales,
                'second_period' => $secondPeriodTotalSales,
                'growth' => number_format($salesGrowth, 2) . '%'
            ],
            'total_duration' => [
                'first_period' => sprintf('%d hours %d minutes', $firstPeriodHours, $firstPeriodMinutes),
                'second_period' => sprintf('%d hours %d minutes', $secondPeriodHours, $secondPeriodMinutes),
                'growth' => number_format($durationGrowth, 2) . '%'
            ],
            'gmv_per_hour' => [
                'first_period' => number_format($firstPeriodGMVPerHour, 2),
                'second_period' => number_format($secondPeriodGMVPerHour, 2),
                'growth' => number_format($gmvPerHourGrowth, 2) . '%'
            ]
        ];

        return response()->json($result);
    }
}
