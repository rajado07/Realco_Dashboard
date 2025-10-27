<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopeeBrandPortalShopData;
use App\Models\DataGroup;
use Illuminate\Support\Facades\Log;


use App\Services\DateRangeService;
use App\Services\DataGroupService;
use App\Services\MetricsCalculationService;

class ShopeeBrandPortalShopDataController extends Controller
{
    protected $dateRangeService;
    protected $dataGroupService;
    protected $metricsCalculationService;

    public function __construct(DateRangeService $dateRangeService, DataGroupService $dataGroupService, MetricsCalculationService $metricsCalculationService)
    {
        $this->dateRangeService = $dateRangeService;
        $this->dataGroupService = $dataGroupService;
        $this->metricsCalculationService = $metricsCalculationService;
    }

    public function index(Request $request)
    {
        // Variabel Tetap
        $type = 'shopee_brand_portal_shop';
        $marketPlaceId = 1;

        $brandId = $request->input('brand_id', null);

        // Menentukan Date Range Yang Harus Diambil
        try {
            $dateRanges = $this->dateRangeService->getDateRange($request);
            [$startDate, $endDate] = $dateRanges['current'];
            [$previousStartDate, $previousEndDate] = $dateRanges['previous'];
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Definisikan metrik yang akan dihitung
        $metricsConfig = [
            'sum' => ['gross_sales', 'gross_orders', 'gross_units_sold', 'product_views', 'product_visitors'],
            'additional' => [
                'average_basket_size' => [
                    'type' => 'ratio',
                    'base' => ['gross_sales', 'gross_orders'], // [numerator, denominator]
                    'multiply_by_100' => false, // Tidak dikali 100
                ],
                'average_selling_price' => [
                    'type' => 'ratio',
                    'base' => ['gross_sales', 'gross_units_sold'], // [numerator, denominator]
                    'multiply_by_100' => false, // Tidak dikali 100
                ],
                'conversion' => [
                    'type' => 'ratio',
                    'base' => ['gross_units_sold', 'product_views'], // [numerator, denominator]
                    'multiply_by_100' => true, // Dikali 100 untuk persentase
                ],
            ],
            'format' => [
                'conversion' => 'percentage',
            ],
            'search_field' => 'product_name', // Default ke 'product_name'
        ];

        // Query data untuk periode current dan previous
        $currentResults = ShopeeBrandPortalShopData::whereBetween('data_date', [$startDate, $endDate])
            ->when($brandId, fn($query) => $query->where('brand_id', $brandId))
            ->get();

        $previousResults = ShopeeBrandPortalShopData::whereBetween('data_date', [$previousStartDate, $previousEndDate])
            ->when($brandId, fn($query) => $query->where('brand_id', $brandId))
            ->get();

        // Dapatkan Group Hierarchy
        $groupHierarchy = $this->dataGroupService->getDataGroups($type, $marketPlaceId, $brandId);

        // Kelompokkan data saat ini dan sebelumnya menggunakan groupData
        $groupedCurrentData = $this->dataGroupService->groupData($currentResults, $groupHierarchy, $metricsConfig['search_field']);
        $groupedPreviousData = $this->dataGroupService->groupData($previousResults, $groupHierarchy, $metricsConfig['search_field']);

        // Hitung Berdasarkan Group Dengan Meneruskan MetricsConfig
        try {
            $output = $this->dataGroupService->countDataByGroup($groupedCurrentData, $groupedPreviousData, $groupHierarchy, $metricsConfig);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($output);
    }

    public function showDataByGroup(Request $request)
    {
        // Variabel Tetap
        $type = 'shopee_brand_portal_shop';
        $marketPlaceId = 1;

        $brandId = $request->input('brand_id', null);

        // Menentukan Date Range Yang Harus Diambil
        try {
            $dateRanges = $this->dateRangeService->getDateRange($request);
            [$startDate, $endDate] = $dateRanges['current'];
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Definisikan field yang ingin diambil dan search_field
        $metricsConfig = [
            'fields' => [
                'id',
                'data_date',
                'product_name',
                'product_id',
                'gross_sales',
                'gross_orders',
                'gross_units_sold',
                'product_views',
                'product_visitors',
            ],
            'search_field' => 'product_name', // Field yang digunakan untuk pengelompokan
        ];

        // Query data untuk periode saat ini
        $currentResults = ShopeeBrandPortalShopData::whereBetween('data_date', [$startDate, $endDate])
            ->when($brandId, fn($query) => $query->where('brand_id', $brandId))
            ->get();

        // Dapatkan Group
        $groupHierarchy = $this->dataGroupService->getDataGroups($type, $marketPlaceId, $brandId);

        // Kelompokkan data menggunakan fungsi groupData
        $groupedData = $this->dataGroupService->groupData($currentResults, $groupHierarchy, $metricsConfig['search_field']);

        // Ambil data berdasarkan grup
        try {
            $groupedDataOutput = $this->dataGroupService->showDataByGroup($groupedData, $groupHierarchy, $metricsConfig);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Return data yang sudah dikelompokkan
        return response()->json(['data' => $groupedDataOutput]);
    }

    public function getSummary(Request $request)
    {
        // Menentukan Date Range Yang Harus Diambil
        try {
            $dateRanges = $this->dateRangeService->getDateRange($request);
            [$startDate, $endDate] = $dateRanges['current'];
            [$previousStartDate, $previousEndDate] = $dateRanges['previous'];
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $brandId = $request->input('brand_id');

        // Metrics configuration
        $metricsConfig = [
            'sum' => ['gross_sales', 'gross_orders', 'gross_units_sold', 'product_views', 'product_visitors'],
            'additional' => [
                'average_basket_size' => [
                    'type' => 'ratio',
                    'base' => ['gross_sales', 'gross_orders'],
                    'multiply_by_100' => false,
                ],
                'average_selling_price' => [
                    'type' => 'ratio',
                    'base' => ['gross_sales', 'gross_units_sold'],
                    'multiply_by_100' => false,
                ],
                'conversion' => [
                    'type' => 'ratio',
                    'base' => ['gross_units_sold', 'product_views'],
                    'multiply_by_100' => true,
                ],
            ],
            'format' => [
                'conversion' => 'percentage',
            ],
        ];

        // Query for current and previous data
        $currentData = ShopeeBrandPortalShopData::whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);
        $previousData = ShopeeBrandPortalShopData::whereBetween('data_date', [$previousStartDate->toDateString(), $previousEndDate->toDateString()]);

        if (!empty($brandId) && $brandId != 0) {
            $currentData->where('brand_id', $brandId);
            $previousData->where('brand_id', $brandId);
        }

        $currentData = $currentData->get();
        $previousData = $previousData->get();

        // Use the service to calculate metrics
        $summary = $this->metricsCalculationService->calculateNowPreviousChange($currentData, $previousData, $metricsConfig);

        return response()->json($summary);
    }

    public function latestRetrievedAt()
    {
        $latestData = ShopeeBrandPortalShopData::orderBy('data_date', 'desc')->first();
        return $latestData ? $latestData->data_date : 'No data available';
    }
}
