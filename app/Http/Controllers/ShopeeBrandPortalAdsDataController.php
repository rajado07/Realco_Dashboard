<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ShopeeBrandPortalAdsData;

class ShopeeBrandPortalAdsDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $query = ShopeeBrandPortalAdsData::select([
            'id',
            'data_date',
            'shop_name',
            'shop_id',
            'impressions',
            'orders',
            'gross_sales',
            'ads_spend',
            'units_sold',
            'brand_id',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $data = $query->get()->map(function ($item) {
            $item->return_on_ads_spend = $item->ads_spend == 0 ? 0 : number_format($item->gross_sales / $item->ads_spend, 2);
            return $item;
        });

        return response()->json($data);
    }

    public function getSummary(Request $request)
    {
        // Mengambil input tanggal dan brand_id
        $startDate = Carbon::parse($request->input('start_date', now()->subMonth()->toDateString()));
        $endDate = Carbon::parse($request->input('end_date', now()->toDateString()));
        $brandId = $request->input('brand_id');

        // Menghitung interval waktu
        $interval = $startDate->diffInDays($endDate) + 1;
        $previousStartDate = $startDate->copy()->subDays($interval);
        $previousEndDate = $endDate->copy()->subDays($interval);

        // Query data untuk periode saat ini dan sebelumnya
        $currentData = $this->fetchData($startDate, $endDate, $brandId);
        $previousData = $this->fetchData($previousStartDate, $previousEndDate, $brandId);

        // Menghitung persentase perubahan
        $changes = $this->calculatePercentageChanges($currentData, $previousData);

        // Menyusun respons
        $summary = [
            'current' => $currentData,
            'previous' => $previousData,
            'changes' => $changes,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'brand_id' => $brandId
        ];

        return response()->json($summary);
    }

    private function fetchData($startDate, $endDate, $brandId)
    {
        $query = ShopeeBrandPortalAdsData::selectRaw(
            'SUM(impressions) as total_impressions,
             SUM(orders) as total_orders,
             SUM(gross_sales) as total_gross_sales,
             SUM(ads_spend) as total_ads_spend,
             SUM(units_sold) as total_units_sold'
        )->whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($brandId && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $result = $query->first();

        // Calculate return on ads spend (ROAS)
        $result->average_roas = $result->total_ads_spend > 0 ? number_format($result->total_gross_sales / $result->total_ads_spend, 2) : 0;

        return $result;
    }

    private function calculatePercentageChanges($currentData, $previousData)
    {
        $changes = [];
        foreach ($currentData->toArray() as $key => $value) {
            if (strpos($key, 'total_') === 0 && isset($previousData->$key)) {
                $prevValue = $previousData->$key;
                $change = $prevValue == 0 ? null : round((($value - $prevValue) / $prevValue) * 100, 2);
                $changes[$key . '_change_percentage'] = $change;
            }
        }
        $changes['average_roas_change'] = $previousData->average_roas == 0 ? null : round((($currentData->average_roas - $previousData->average_roas) / $previousData->average_roas) * 100, 2);
        return $changes;
    }


    public function latestRetrievedAt()
    {
        $latestData = ShopeeBrandPortalAdsData::orderBy('retrieved_at', 'desc')->first();
        return $latestData ? $latestData->retrieved_at : 'No data available';
    }
}
