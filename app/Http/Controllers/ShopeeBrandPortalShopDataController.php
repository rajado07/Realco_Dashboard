<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;

class ShopeeBrandPortalShopDataController extends Controller
{

    public function getDataGroups($brandId = null)
    {
        return DataGroup::where('type', 'shopee_brand_portal_shop')
            ->where('market_place_id', 1) // Shopee
            ->when($brandId && $brandId != 0, function ($query) use ($brandId) {
                // Jika brand_id tidak bernilai 0/null, filter di sini
                $query->where('brand_id', $brandId);
            })
            ->whereNull('parent_id')
            ->with('children')
            ->get();
    }

    private function flattenGroupHierarchy($dataGroups)
    {
        $flattened = collect();

        foreach ($dataGroups as $group) {
            $flattened->push($group);
            foreach ($group->children as $child) {
                $flattened->push($child);
            }
        }

        return $flattened;
    }

    public function index(Request $request)
    {
        // 1. Tentukan startDate & endDate
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        if (!$startDateInput && !$endDateInput) {
            $startDate = Carbon::now()->subMonth()->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
        } else {
            try {
                $startDate = Carbon::parse($startDateInput)->startOfDay();
                $endDate   = Carbon::parse($endDateInput)->endOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format'], 400);
            }
        }

        // Hitung selisih hari untuk menentukan periode sebelumnya
        $diffInDays = $startDate->diffInDays($endDate) + 1;

        $previousStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
        $previousEndDate   = $startDate->copy()->subDays(1)->endOfDay();

        // 2. Tangkap brand_id
        $brandId = $request->input('brand_id', null);

        // 3. Query data "current" dan "previous"
        $results = ShopeeBrandPortalShopData::whereBetween('data_date', [$startDate, $endDate])
            ->when($brandId, fn($query) => $query->where('brand_id', $brandId))
            ->get();

        $previousResults = ShopeeBrandPortalShopData::whereBetween('data_date', [$previousStartDate, $previousEndDate])
            ->when($brandId, fn($query) => $query->where('brand_id', $brandId))
            ->get();

        // 4. Ambil semua data groups
        $dataGroups = $this->getDataGroups($brandId);

        // 5. Buat daftar DataGroups dalam urutan hierarki (parent terlebih dahulu)
        $groupHierarchy = $this->flattenGroupHierarchy($dataGroups);

        // 6. Alokasikan setiap item ke grup yang sesuai
        $groupedCurrent = [];
        $groupedPrevious = [];
        $assignedCurrentIds = [];
        $assignedPreviousIds = [];

        foreach ($results as $item) {
            foreach ($groupHierarchy as $group) {
                $keyword = strtolower($group->keyword);
                if ($keyword && strpos(strtolower($item->product_name), $keyword) !== false) {
                    $groupedCurrent[$group->id][] = $item;
                    $assignedCurrentIds[] = $item->id;
                    break; // Alokasikan ke grup pertama yang cocok
                }
            }
        }

        foreach ($previousResults as $item) {
            foreach ($groupHierarchy as $group) {
                $keyword = strtolower($group->keyword);
                if ($keyword && strpos(strtolower($item->product_name), $keyword) !== false) {
                    $groupedPrevious[$group->id][] = $item;
                    $assignedPreviousIds[] = $item->id;
                    break; // Alokasikan ke grup pertama yang cocok
                }
            }
        }

        // 7. Kumpulkan data "Others"
        $othersCurrent = $results->whereNotIn('id', $assignedCurrentIds);
        $othersPrevious = $previousResults->whereNotIn('id', $assignedPreviousIds);

        // 8. Fungsi rekursif untuk menghitung data grup
        $computeGroupData = function ($group) use (&$computeGroupData, $groupedCurrent, $groupedPrevious) {
            // Hitung data untuk grup ini
            $currentData = isset($groupedCurrent[$group->id]) ? collect($groupedCurrent[$group->id]) : collect();
            $previousData = isset($groupedPrevious[$group->id]) ? collect($groupedPrevious[$group->id]) : collect();

            // Hitung totals untuk current dan previous
            $currentTotals = [
                'gross_sales'           => $currentData->sum('gross_sales'),
                'gross_orders'          => $currentData->sum('gross_orders'),
                'gross_units_sold'      => $currentData->sum('gross_units_sold'),
                'product_views'         => $currentData->sum('product_views'),
                'product_visitors'      => $currentData->sum('product_visitors'),
            ];

            $previousTotals = [
                'gross_sales'           => $previousData->sum('gross_sales'),
                'gross_orders'          => $previousData->sum('gross_orders'),
                'gross_units_sold'      => $previousData->sum('gross_units_sold'),
                'product_views'         => $previousData->sum('product_views'),
                'product_visitors'      => $previousData->sum('product_visitors'),
            ];

            // Rekursif untuk children
            $childrenData = $group->children->map(function ($child) use ($computeGroupData) {
                return $computeGroupData($child);
            });

            // Tambahkan data anak ke parent (akumulasi metrics)
            foreach ($childrenData as $childData) {
                foreach (['gross_sales', 'gross_orders', 'gross_units_sold', 'product_views', 'product_visitors'] as $key) {
                    $currentTotals[$key]  += $childData[$key]['now'];
                    $previousTotals[$key] += $childData[$key]['previous'];
                }
            }

            // Setelah akumulasi, hitung metrik tambahan
            $currentTotals['average_basket_size']   = $currentTotals['gross_orders'] > 0
                ? $currentTotals['gross_sales'] / $currentTotals['gross_orders']
                : 0;
            $currentTotals['average_selling_price'] = $currentTotals['gross_units_sold'] > 0
                ? $currentTotals['gross_sales'] / $currentTotals['gross_units_sold']
                : 0;
            $currentTotals['conversion']            = $currentTotals['product_views'] > 0
                ? ($currentTotals['gross_units_sold'] / $currentTotals['product_views']) * 100
                : 0;

            $previousTotals['average_basket_size']   = $previousTotals['gross_orders'] > 0
                ? $previousTotals['gross_sales'] / $previousTotals['gross_orders']
                : 0;
            $previousTotals['average_selling_price'] = $previousTotals['gross_units_sold'] > 0
                ? $previousTotals['gross_sales'] / $previousTotals['gross_units_sold']
                : 0;
            $previousTotals['conversion']            = $previousTotals['product_views'] > 0
                ? ($previousTotals['gross_units_sold'] / $previousTotals['product_views']) * 100
                : 0;

            // Hitung perubahan
            $changes = [];
            foreach ($currentTotals as $key => $currentValue) {
                if (in_array($key, ['average_basket_size', 'average_selling_price', 'conversion'])) {
                    $previousValue = $previousTotals[$key] ?? 0;
                    $changes[$key] = $previousValue > 0
                        ? (($currentValue - $previousValue) / $previousValue) * 100
                        : ($currentValue > 0 ? 100 : 0);
                } else {
                    $previousValue = $previousTotals[$key] ?? 0;
                    $changes[$key] = $previousValue > 0
                        ? (($currentValue - $previousValue) / $previousValue) * 100
                        : ($currentValue > 0 ? 100 : 0);
                }
            }

            // Formatting conversion sebagai persen
            $currentTotals['conversion']  = number_format($currentTotals['conversion'], 2) . '%';
            $previousTotals['conversion'] = number_format($previousTotals['conversion'], 2) . '%';
            $changes['conversion']        = number_format($changes['conversion'], 2) . '%';

            return [
                'data_group_name'       => $group->name,
                'gross_sales'           => [
                    'now'      => $currentTotals['gross_sales'],
                    'previous' => $previousTotals['gross_sales'],
                    'change'   => $changes['gross_sales'],
                ],
                'gross_orders'          => [
                    'now'      => $currentTotals['gross_orders'],
                    'previous' => $previousTotals['gross_orders'],
                    'change'   => $changes['gross_orders'],
                ],
                'gross_units_sold'      => [
                    'now'      => $currentTotals['gross_units_sold'],
                    'previous' => $previousTotals['gross_units_sold'],
                    'change'   => $changes['gross_units_sold'],
                ],
                'product_views'         => [
                    'now'      => $currentTotals['product_views'],
                    'previous' => $previousTotals['product_views'],
                    'change'   => $changes['product_views'],
                ],
                'product_visitors'      => [
                    'now'      => $currentTotals['product_visitors'],
                    'previous' => $previousTotals['product_visitors'],
                    'change'   => $changes['product_visitors'],
                ],
                'average_basket_size'   => [
                    'now'      => $currentTotals['average_basket_size'],
                    'previous' => $previousTotals['average_basket_size'],
                    'change'   => $changes['average_basket_size'],
                ],
                'average_selling_price' => [
                    'now'      => $currentTotals['average_selling_price'],
                    'previous' => $previousTotals['average_selling_price'],
                    'change'   => $changes['average_selling_price'],
                ],
                'conversion'            => [
                    'now'      => $currentTotals['conversion'],
                    'previous' => $previousTotals['conversion'],
                    'change'   => $changes['conversion'],
                ],
                'children'              => $childrenData,
            ];
        };

        // 9. Proses semua parent groups
        $output = $dataGroups
            ->filter(fn($group) => is_null($group->parent_id))
            ->map(function ($parentGroup) use ($computeGroupData) {
                return $computeGroupData($parentGroup);
            })
            ->values();

        // 10. Hitung data "Others"
        $othersCurrentTotals = [
            'gross_sales'           => $othersCurrent->sum('gross_sales'),
            'gross_orders'          => $othersCurrent->sum('gross_orders'),
            'gross_units_sold'      => $othersCurrent->sum('gross_units_sold'),
            'product_views'         => $othersCurrent->sum('product_views'),
            'product_visitors'      => $othersCurrent->sum('product_visitors'),
        ];

        $previousTotalsOthers = [
            'gross_sales'           => $othersPrevious->sum('gross_sales'),
            'gross_orders'          => $othersPrevious->sum('gross_orders'),
            'gross_units_sold'      => $othersPrevious->sum('gross_units_sold'),
            'product_views'         => $othersPrevious->sum('product_views'),
            'product_visitors'      => $othersPrevious->sum('product_visitors'),
        ];

        // Hitung metrik tambahan untuk "Others"
        $othersCurrentTotals['average_basket_size']   = $othersCurrentTotals['gross_orders'] > 0
            ? $othersCurrentTotals['gross_sales'] / $othersCurrentTotals['gross_orders']
            : 0;
        $othersCurrentTotals['average_selling_price'] = $othersCurrentTotals['gross_units_sold'] > 0
            ? $othersCurrentTotals['gross_sales'] / $othersCurrentTotals['gross_units_sold']
            : 0;
        $othersCurrentTotals['conversion']            = $othersCurrentTotals['product_views'] > 0
            ? ($othersCurrentTotals['gross_units_sold'] / $othersCurrentTotals['product_views']) * 100
            : 0;

        $previousTotalsOthers['average_basket_size']   = $previousTotalsOthers['gross_orders'] > 0
            ? $previousTotalsOthers['gross_sales'] / $previousTotalsOthers['gross_orders']
            : 0;
        $previousTotalsOthers['average_selling_price'] = $previousTotalsOthers['gross_units_sold'] > 0
            ? $previousTotalsOthers['gross_sales'] / $previousTotalsOthers['gross_units_sold']
            : 0;
        $previousTotalsOthers['conversion']            = $previousTotalsOthers['product_views'] > 0
            ? ($previousTotalsOthers['gross_units_sold'] / $previousTotalsOthers['product_views']) * 100
            : 0;

        // Hitung perubahan untuk "Others"
        $othersChanges = [];
        foreach ($othersCurrentTotals as $key => $currentValue) {
            $previousValue = $previousTotalsOthers[$key] ?? 0;
            $othersChanges[$key] = $previousValue > 0
                ? (($currentValue - $previousValue) / $previousValue) * 100
                : ($currentValue > 0 ? 100 : 0);
        }

        // Formatting conversion sebagai persen
        $othersTotalsFormatted = [
            'now'      => [
                'gross_sales'           => $othersCurrentTotals['gross_sales'],
                'gross_orders'          => $othersCurrentTotals['gross_orders'],
                'gross_units_sold'      => $othersCurrentTotals['gross_units_sold'],
                'product_views'         => $othersCurrentTotals['product_views'],
                'product_visitors'      => $othersCurrentTotals['product_visitors'],
                'average_basket_size'   => $othersCurrentTotals['average_basket_size'],
                'average_selling_price' => $othersCurrentTotals['average_selling_price'],
                'conversion'            => number_format($othersCurrentTotals['conversion'], 2) . '%',
            ],
            'previous' => [
                'gross_sales'           => $previousTotalsOthers['gross_sales'],
                'gross_orders'          => $previousTotalsOthers['gross_orders'],
                'gross_units_sold'      => $previousTotalsOthers['gross_units_sold'],
                'product_views'         => $previousTotalsOthers['product_views'],
                'product_visitors'      => $previousTotalsOthers['product_visitors'],
                'average_basket_size'   => $previousTotalsOthers['average_basket_size'],
                'average_selling_price' => $previousTotalsOthers['average_selling_price'],
                'conversion'            => number_format($previousTotalsOthers['conversion'], 2) . '%',
            ],
            'change' => [
                'gross_sales'           => $othersChanges['gross_sales'],
                'gross_orders'          => $othersChanges['gross_orders'],
                'gross_units_sold'      => $othersChanges['gross_units_sold'],
                'product_views'         => $othersChanges['product_views'],
                'product_visitors'      => $othersChanges['product_visitors'],
                'average_basket_size'   => $othersChanges['average_basket_size'],
                'average_selling_price' => $othersChanges['average_selling_price'],
                'conversion'            => number_format($othersChanges['conversion'], 2) . '%',
            ],
        ];

        // 11. Tambahkan "Others" ke output jika ada data
        if ($othersCurrent->isNotEmpty() || $othersPrevious->isNotEmpty()) {
            $output->push([
                'data_group_name' => 'Others',
                'gross_sales' => [
                    'now'      => $othersTotalsFormatted['now']['gross_sales'],
                    'previous' => $othersTotalsFormatted['previous']['gross_sales'],
                    'change'   => $othersTotalsFormatted['change']['gross_sales'],
                ],
                'gross_orders' => [
                    'now'      => $othersTotalsFormatted['now']['gross_orders'],
                    'previous' => $othersTotalsFormatted['previous']['gross_orders'],
                    'change'   => $othersTotalsFormatted['change']['gross_orders'],
                ],
                'gross_units_sold' => [
                    'now'      => $othersTotalsFormatted['now']['gross_units_sold'],
                    'previous' => $othersTotalsFormatted['previous']['gross_units_sold'],
                    'change'   => $othersTotalsFormatted['change']['gross_units_sold'],
                ],
                'product_views' => [
                    'now'      => $othersTotalsFormatted['now']['product_views'],
                    'previous' => $othersTotalsFormatted['previous']['product_views'],
                    'change'   => $othersTotalsFormatted['change']['product_views'],
                ],
                'product_visitors' => [
                    'now'      => $othersTotalsFormatted['now']['product_visitors'],
                    'previous' => $othersTotalsFormatted['previous']['product_visitors'],
                    'change'   => $othersTotalsFormatted['change']['product_visitors'],
                ],
                'average_basket_size' => [
                    'now'      => $othersTotalsFormatted['now']['average_basket_size'],
                    'previous' => $othersTotalsFormatted['previous']['average_basket_size'],
                    'change'   => $othersTotalsFormatted['change']['average_basket_size'],
                ],
                'average_selling_price' => [
                    'now'      => $othersTotalsFormatted['now']['average_selling_price'],
                    'previous' => $othersTotalsFormatted['previous']['average_selling_price'],
                    'change'   => $othersTotalsFormatted['change']['average_selling_price'],
                ],
                'conversion' => [
                    'now'      => $othersTotalsFormatted['now']['conversion'],
                    'previous' => $othersTotalsFormatted['previous']['conversion'],
                    'change'   => $othersTotalsFormatted['change']['conversion'],
                ],
                'children' => [],
            ]);
        }

        return response()->json($output);
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
