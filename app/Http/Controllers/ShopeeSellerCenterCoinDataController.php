<?php

namespace App\Http\Controllers;

use App\Models\ShopeeSellerCenterCoinData;
use App\Models\ShopeeSellerCenterLiveStreamingData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShopeeSellerCenterCoinDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $coinQuery = ShopeeSellerCenterCoinData::select([
            'id',
            'data_date',
            'time',
            'name',
            'coins_amount',
            'brand_id',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $coinQuery->where('brand_id', $brandId);
        }

        $coinData = $coinQuery->get();

        $liveStreamingQuery = ShopeeSellerCenterLiveStreamingData::select([
            'data_date',
            'sales',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $liveStreamingQuery->where('brand_id', $brandId);
        }

        $liveStreamingData = $liveStreamingQuery->get()->groupBy('data_date');

        // Grouping coin data by 'data_date' and aggregating coins_amount
        $groupedData = $coinData->groupBy('data_date')->map(function ($dateGroup) use ($liveStreamingData) {
            $dataDate = $dateGroup->first()->data_date;
            $totalSales = $liveStreamingData->has($dataDate) ? $liveStreamingData[$dataDate]->sum('sales') : 0;
            $totalCoinsAmount = abs($dateGroup->sum('coins_amount')); // Convert to positive

            // Calculate ROI and round to 2 decimal places
            $roi = $totalCoinsAmount != 0 ? round($totalSales / $totalCoinsAmount, 2) : 0;

            return [
                'data_date' => $dataDate,
                'total_coins_amount' => $totalCoinsAmount,
                'total_sales' => $totalSales,
                'roi' => $roi,
                'brand_id' => $dateGroup->first()->brand_id, // Assuming all entries in a group share the same brand_id
                'details' => $dateGroup->map(function ($item) {
                    return [
                        'time' => $item->time,
                        'name' => $item->name,
                        'coins_amount' => $item->coins_amount
                    ];
                })->values()->all()
            ];
        })->values()->all();

        return response()->json($groupedData);
    }


    public function summary(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->subMonth()->toDateString()));
        $endDate = Carbon::parse($request->input('end_date', now()->toDateString()));
        $brandId = $request->input('brand_id');

        $interval = $startDate->diffInDays($endDate) + 1;
        $previousStartDate = $startDate->copy()->subDays($interval);
        $previousEndDate = $endDate->copy()->subDays($interval);

        $currentData = $this->fetchCoinData($startDate, $endDate, $brandId);
        $previousData = $this->fetchCoinData($previousStartDate, $previousEndDate, $brandId);

        $changes = $this->calculatePercentageChanges($currentData, $previousData);

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

    private function fetchCoinData($startDate, $endDate, $brandId)
    {
        $coinQuery = ShopeeSellerCenterCoinData::selectRaw(
            'SUM(ABS(coins_amount)) as total_coins_amount' // Use ABS to convert negative to positive
        )->whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($brandId && $brandId != 0) {
            $coinQuery->where('brand_id', $brandId);
        }

        $coinData = $coinQuery->first();

        $liveStreamingQuery = ShopeeSellerCenterLiveStreamingData::selectRaw(
            'SUM(sales) as total_sales'
        )->whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($brandId && $brandId != 0) {
            $liveStreamingQuery->where('brand_id', $brandId);
        }

        $liveStreamingData = $liveStreamingQuery->first();

        $totalCoinsAmount = $coinData->total_coins_amount ?? 0;
        $totalSales = $liveStreamingData->total_sales ?? 0;
        $averageRoi = $totalCoinsAmount != 0 ? round($totalSales / $totalCoinsAmount, 2) : 0;

        return (object) [
            'total_coins_amount' => $totalCoinsAmount,
            'total_sales' => $totalSales,
            'average_roi' => $averageRoi
        ];
    }

    private function calculatePercentageChanges($currentData, $previousData)
    {
        $changes = [];
        foreach ($currentData as $key => $value) {
            $prevValue = $previousData->$key ?? 0;
            $change = $prevValue == 0 ? null : round((($value - $prevValue) / $prevValue) * 100, 2);
            $changes[$key . '_change_percentage'] = $change;
        }
        return $changes;
    }
}
