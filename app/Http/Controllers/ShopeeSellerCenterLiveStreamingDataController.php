<?php

namespace App\Http\Controllers;

use App\Models\ShopeeSellerCenterLiveStreamingData;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ShopeeSellerCenterLiveStreamingDataController extends Controller
{

    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $query = ShopeeSellerCenterLiveStreamingData::select([
            'data_date',
            'name',
            'duration',
            'unique_viewers',
            'peak_viewers',
            'avg_watch_time',
            'orders',
            'sales',
            'brand_id',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $results = $query->get();

        // Mendapatkan total untuk hari sebelumnya untuk setiap hari dalam rentang tanggal
        $previousDayResults = $results->groupBy('data_date')->map(function ($items, $date) use ($results, $brandId) {
            $previousDate = Carbon::parse($date)->subDay()->toDateString();
            $previousDayItems = ShopeeSellerCenterLiveStreamingData::select([
                'duration',
                'unique_viewers',
                'peak_viewers',
                'avg_watch_time',
                'orders',
                'sales',
            ])
                ->where('data_date', $previousDate);

            if (!is_null($brandId) && $brandId != 0) {
                $previousDayItems->where('brand_id', $brandId);
            }

            $previousDayItems = $previousDayItems->get();

            return [
                'duration' => $previousDayItems->sum('duration'),
                'unique_viewers' => $previousDayItems->sum('unique_viewers'),
                'peak_viewers' => $previousDayItems->avg('peak_viewers'),
                'avg_watch_time' => $previousDayItems->avg('avg_watch_time'),
                'orders' => $previousDayItems->sum('orders'),
                'sales' => $previousDayItems->sum('sales'),
            ];
        });

        // Group by 'data_date' and calculate aggregates
        $groupedResults = $results->groupBy('data_date')->map(function ($items, $date) use ($previousDayResults) {
            $currentTotals = [
                'duration' => $items->sum('duration'),
                'unique_viewers' => $items->sum('unique_viewers'),
                'peak_viewers' => $items->avg('peak_viewers'),
                'avg_watch_time' => $items->avg('avg_watch_time'),
                'orders' => $items->sum('orders'),
                'sales' => $items->sum('sales'),
            ];

            // Get the previous day's totals
            $previousTotals = $previousDayResults->get($date, [
                'duration' => 0,
                'unique_viewers' => 0,
                'peak_viewers' => 0,
                'avg_watch_time' => 0,
                'orders' => 0,
                'sales' => 0,
            ]);

            $changes = [];
            foreach ($currentTotals as $key => $currentValue) {
                $previousValue = $previousTotals[$key];
                $changes[$key] = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;
            }

            return [
                'data_date' => $date,
                'duration' => [
                    'now' => $currentTotals['duration'],
                    'previous' => $previousTotals['duration'],
                    'change' => $changes['duration'],
                ],
                'unique_viewers' => [
                    'now' => $currentTotals['unique_viewers'],
                    'previous' => $previousTotals['unique_viewers'],
                    'change' => $changes['unique_viewers'],
                ],
                'peak_viewers' => [
                    'now' => $currentTotals['peak_viewers'],
                    'previous' => $previousTotals['peak_viewers'],
                    'change' => $changes['peak_viewers'],
                ],
                'avg_watch_time' => [
                    'now' => $currentTotals['avg_watch_time'],
                    'previous' => $previousTotals['avg_watch_time'],
                    'change' => $changes['avg_watch_time'],
                ],
                'orders' => [
                    'now' => $currentTotals['orders'],
                    'previous' => $previousTotals['orders'],
                    'change' => $changes['orders'],
                ],
                'sales' => [
                    'now' => $currentTotals['sales'],
                    'previous' => $previousTotals['sales'],
                    'change' => $changes['sales'],
                ],
                'brand_id' => $items->first()->brand_id, // Assuming all items in a group have the same brand_id
                'details' => $items->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'duration' => $item->duration,
                        'unique_viewers' => $item->unique_viewers,
                        'peak_viewers' => $item->peak_viewers,
                        'avg_watch_time' => $item->avg_watch_time,
                        'orders' => $item->orders,
                        'sales' => $item->sales,
                    ];
                })
            ];
        })->values(); // Use values() to reset the keys

        return response()->json($groupedResults);
    }
}
