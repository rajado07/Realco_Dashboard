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

    public function getDataGroups()
    {
        return DataGroup::where('type', 'shopee_brand_portal_shop')
            ->where('market_place_id', 1) // Shopee
            ->whereNull('parent_id')
            ->with('children')
            ->get();
    }

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
        // Ambil date range
        list($firstPeriodStart, $firstPeriodEnd, $secondPeriodStart, $secondPeriodEnd) = $this->getDateRanges($request);

        // Ambil data ShopeeBrandPortalShopData
        $brandId = $request->input('brandId', 0);
        $dataQuery = ShopeeBrandPortalShopData::whereBetween('data_date', [$secondPeriodStart, $firstPeriodEnd]);
        if ($brandId != 0) {
            $dataQuery->where('brand_id', $brandId);
        }
        $data = $dataQuery->get(); // Kumpulkan dalam collection

        // Ambil parent groups dengan filter berdasarkan brand_id
        $parentGroupsQuery = $this->getDataGroups();
        if ($brandId != 0) {
            $parentGroupsQuery = $parentGroupsQuery->filter(function ($group) use ($brandId) {
                return $group->brand_id == $brandId;
            });
        }
        $parentGroups = $parentGroupsQuery;

        // Siapkan array hasil & collection penanda data terpakai
        $result = [];
        $processedItems = collect();

        // Closure untuk hitung growth (dipakai berulang)
        $calculateGrowth = function ($first, $second) {
            if ($second > 0) {
                return (($first - $second) / $second) * 100;
            }
            return 0;
        };

        // Closure untuk hitung growth conversion (dipakai berulang)
        $calculateConversionGrowth = function ($fpViews, $fpUnits, $spViews, $spUnits) {
            if ($fpViews > 0 && $spViews > 0 && $spUnits > 0) {
                $firstConv = $fpUnits / $fpViews;
                $secondConv = $spUnits / $spViews;
                if ($secondConv != 0) {
                    return (($firstConv - $secondConv) / $secondConv) * 100;
                }
            }
            return 0;
        };

        // Closure rekursif untuk menghitung stats group + children
        $computeGroupStats = function ($group, $data, $processedItems) use (
            &$computeGroupStats,  // Rekursif panggil dirinya sendiri
            $calculateGrowth,
            $calculateConversionGrowth,
            $firstPeriodStart,
            $firstPeriodEnd,
            $secondPeriodStart,
            $secondPeriodEnd
        ) {
            // Siapkan struktur default
            $resultGroup = [
                'data_group_name' => $group->name,
                'product_views'   => ['first_period' => 0, 'second_period' => 0, 'growth' => '0%'],
                'conversion'      => ['first_period' => '0%', 'second_period' => '0%', 'growth' => '0%'],
                'gmv'             => ['first_period' => 0, 'second_period' => 0, 'growth' => '0%'],
                'children'        => [],
            ];

            $keyword = $group->keyword;

            // Filter data berdasarkan keyword
            $filteredData = collect([]);
            if ($keyword) {
                $filteredData = $data->filter(function ($item) use ($keyword) {
                    return strpos(strtolower($item->product_name), strtolower($keyword)) !== false;
                });
            }

            // Tandai data sebagai sudah diproses
            $processedItems->push(...$filteredData->all());

            // Pisahkan data untuk periode pertama
            $firstPeriodItems = $filteredData->filter(function ($item) use ($firstPeriodStart, $firstPeriodEnd) {
                return Carbon::parse($item->data_date)->between($firstPeriodStart, $firstPeriodEnd);
            });
            $fpProductViews = $firstPeriodItems->sum('product_views');
            $fpUnitsSold    = $firstPeriodItems->sum('gross_units_sold');
            $fpSales        = $firstPeriodItems->sum('gross_sales');

            // Pisahkan data untuk periode kedua
            $secondPeriodItems = $filteredData->filter(function ($item) use ($secondPeriodStart, $secondPeriodEnd) {
                return Carbon::parse($item->data_date)->between($secondPeriodStart, $secondPeriodEnd);
            });
            $spProductViews = $secondPeriodItems->sum('product_views');
            $spUnitsSold    = $secondPeriodItems->sum('gross_units_sold');
            $spSales        = $secondPeriodItems->sum('gross_sales');

            // Hitung growth
            $viewsGrowth = $calculateGrowth($fpProductViews, $spProductViews);
            $convGrowth  = $calculateConversionGrowth($fpProductViews, $fpUnitsSold, $spProductViews, $spUnitsSold);
            $gmvGrowth   = $calculateGrowth($fpSales, $spSales);

            // Masukkan hasil ke $resultGroup
            $resultGroup['product_views'] = [
                'first_period' => $fpProductViews,
                'second_period' => $spProductViews,
                'growth' => number_format($viewsGrowth, 2) . '%'
            ];
            $resultGroup['conversion'] = [
                'first_period' => $fpProductViews > 0
                    ? number_format(($fpUnitsSold / $fpProductViews) * 100, 2) . '%'
                    : '0%',
                'second_period' => $spProductViews > 0
                    ? number_format(($spUnitsSold / $spProductViews) * 100, 2) . '%'
                    : '0%',
                'growth' => number_format($convGrowth, 2) . '%'
            ];
            $resultGroup['gmv'] = [
                'first_period' => $fpSales,
                'second_period' => $spSales,
                'growth' => number_format($gmvGrowth, 2) . '%'
            ];

            // Jika ada children, proses rekursif
            if ($group->children->count() > 0) {
                foreach ($group->children as $child) {
                    $childStats = $computeGroupStats($child, $data, $processedItems);

                    // Tambahkan data children ke parent
                    $resultGroup['product_views']['first_period'] += $childStats['product_views']['first_period'];
                    $resultGroup['product_views']['second_period'] += $childStats['product_views']['second_period'];
                    $resultGroup['gmv']['first_period'] += $childStats['gmv']['first_period'];
                    $resultGroup['gmv']['second_period'] += $childStats['gmv']['second_period'];

                    // Tambahkan children ke hasil
                    $resultGroup['children'][] = $childStats;
                }

                // Hitung ulang growth setelah penambahan data children
                $resultGroup['product_views']['growth'] = number_format(
                    $calculateGrowth($resultGroup['product_views']['first_period'], $resultGroup['product_views']['second_period']),
                    2
                ) . '%';
                $resultGroup['gmv']['growth'] = number_format(
                    $calculateGrowth($resultGroup['gmv']['first_period'], $resultGroup['gmv']['second_period']),
                    2
                ) . '%';
            }

            return $resultGroup;
        };

        // Proses setiap parent group
        foreach ($parentGroups as $parent) {
            $groupStats = $computeGroupStats($parent, $data, $processedItems);
            $result[] = $groupStats;
        }

        // Kembalikan response JSON
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
