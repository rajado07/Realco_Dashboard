<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\MetaCpasData;

class MetaCpasDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);
        $marketPlaceId = $request->input('market_place_id', null);

        $query = MetaCpasData::with('dataGroup') // Include the relationship
            ->select([
                'id',
                'data_date',
                'ad_set_id',
                'ad_set_name',
                'ad_name',
                'amount_spent',
                'content_views_with_shared_items',
                'adds_to_cart_with_shared_items',
                'purchases_with_shared_items',
                'purchases_conversion_value_for_shared_items_only',
                'impressions',
                'brand_id',
                'market_place_id',
                'data_group_id',
            ])
            ->selectRaw('IF(amount_spent > 0, purchases_conversion_value_for_shared_items_only / amount_spent, 0) as return_on_ad_spend')
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        if (!is_null($marketPlaceId) && $marketPlaceId != 0) {
            $query->where('market_place_id', $marketPlaceId);
        }

        $results = $query->get();

        // Calculate previous period totals
        $previousStartDate = Carbon::parse($startDate)->subMonth()->toDateString();
        $previousEndDate = Carbon::parse($endDate)->subMonth()->toDateString();

        $previousQuery = MetaCpasData::with('dataGroup') // Include the relationship
            ->select([
                'data_group_id',
                'amount_spent',
                'content_views_with_shared_items',
                'adds_to_cart_with_shared_items',
                'purchases_with_shared_items',
                'purchases_conversion_value_for_shared_items_only',
                'impressions',
            ])
            ->selectRaw('IF(amount_spent > 0, purchases_conversion_value_for_shared_items_only / amount_spent, 0) as return_on_ad_spend')
            ->whereBetween('data_date', [$previousStartDate, $previousEndDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $previousQuery->where('brand_id', $brandId);
        }

        if (!is_null($marketPlaceId) && $marketPlaceId != 0) {
            $previousQuery->where('market_place_id', $marketPlaceId);
        }

        $previousResults = $previousQuery->get();

        // Group by 'data_group_id' and calculate aggregates
        $groupedResults = $results->groupBy('data_group_id')->map(function ($items, $groupId) use ($previousResults) {
            $currentTotals = [
                'amount_spent' => $items->sum('amount_spent'),
                'content_views_with_shared_items' => $items->sum('content_views_with_shared_items'),
                'adds_to_cart_with_shared_items' => $items->sum('adds_to_cart_with_shared_items'),
                'purchases_with_shared_items' => $items->sum('purchases_with_shared_items'),
                'purchases_conversion_value_for_shared_items_only' => $items->sum('purchases_conversion_value_for_shared_items_only'),
                'impressions' => $items->sum('impressions'),
                'return_on_ad_spend' => $items->sum('purchases_conversion_value_for_shared_items_only') / max($items->sum('amount_spent'), 1),
            ];

            // Get previous totals for the same group
            $previousItems = $previousResults->where('data_group_id', $groupId);
            $previousTotals = [
                'amount_spent' => $previousItems->sum('amount_spent'),
                'content_views_with_shared_items' => $previousItems->sum('content_views_with_shared_items'),
                'adds_to_cart_with_shared_items' => $previousItems->sum('adds_to_cart_with_shared_items'),
                'purchases_with_shared_items' => $previousItems->sum('purchases_with_shared_items'),
                'purchases_conversion_value_for_shared_items_only' => $previousItems->sum('purchases_conversion_value_for_shared_items_only'),
                'impressions' => $previousItems->sum('impressions'),
                'return_on_ad_spend' => $previousItems->sum('purchases_conversion_value_for_shared_items_only') / max($previousItems->sum('amount_spent'), 1),
            ];

            $changes = [];
            foreach ($currentTotals as $key => $currentValue) {
                $previousValue = $previousTotals[$key];
                $changes[$key] = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;
            }

            return [
                'data_group_name' => $items->first()->dataGroup->name ?? 'Unknown Group', // Get group name with null check
                'amount_spent' => [
                    'now' => $currentTotals['amount_spent'],
                    'previous' => $previousTotals['amount_spent'],
                    'change' => $changes['amount_spent'],
                ],
                'content_views_with_shared_items' => [
                    'now' => $currentTotals['content_views_with_shared_items'],
                    'previous' => $previousTotals['content_views_with_shared_items'],
                    'change' => $changes['content_views_with_shared_items'],
                ],
                'adds_to_cart_with_shared_items' => [
                    'now' => $currentTotals['adds_to_cart_with_shared_items'],
                    'previous' => $previousTotals['adds_to_cart_with_shared_items'],
                    'change' => $changes['adds_to_cart_with_shared_items'],
                ],
                'purchases_with_shared_items' => [
                    'now' => $currentTotals['purchases_with_shared_items'],
                    'previous' => $previousTotals['purchases_with_shared_items'],
                    'change' => $changes['purchases_with_shared_items'],
                ],
                'purchases_conversion_value_for_shared_items_only' => [
                    'now' => $currentTotals['purchases_conversion_value_for_shared_items_only'],
                    'previous' => $previousTotals['purchases_conversion_value_for_shared_items_only'],
                    'change' => $changes['purchases_conversion_value_for_shared_items_only'],
                ],
                'impressions' => [
                    'now' => $currentTotals['impressions'],
                    'previous' => $previousTotals['impressions'],
                    'change' => $changes['impressions'],
                ],
                'return_on_ad_spend' => [
                    'now' => $currentTotals['return_on_ad_spend'],
                    'previous' => $previousTotals['return_on_ad_spend'],
                    'change' => $changes['return_on_ad_spend'],
                ],
                'details' => $items->map(function ($item) {
                    return [
                        'ad_set_id' => $item->ad_set_id,
                        'ad_set_name' => $item->ad_set_name,
                        'ad_name' => $item->ad_name,
                        'amount_spent' => $item->amount_spent,
                        'content_views_with_shared_items' => $item->content_views_with_shared_items,
                        'adds_to_cart_with_shared_items' => $item->adds_to_cart_with_shared_items,
                        'purchases_with_shared_items' => $item->purchases_with_shared_items,
                        'purchases_conversion_value_for_shared_items_only' => $item->purchases_conversion_value_for_shared_items_only,
                        'impressions' => $item->impressions,
                        'return_on_ad_spend' => $item->amount_spent > 0 ? $item->purchases_conversion_value_for_shared_items_only / $item->amount_spent : 0,
                        'brand_id' => $item->brand_id,
                        'market_place_id' => $item->market_place_id,
                    ];
                })
            ];
        })->values(); // Use values() to reset the keys

        return response()->json($groupedResults);
    }

    public function getSummary(Request $request)
    {
        // Mengambil input tanggal, brand_id, dan market_place_id
        $startDate = Carbon::parse($request->input('start_date', now()->subMonth()->toDateString()));
        $endDate = Carbon::parse($request->input('end_date', now()->toDateString()));
        $brandId = $request->input('brand_id');
        $marketPlaceId = $request->input('market_place_id');

        // Menghitung interval waktu
        $interval = $startDate->diffInDays($endDate) + 1;
        $previousStartDate = $startDate->copy()->subDays($interval);
        $previousEndDate = $endDate->copy()->subDays($interval);

        // Query data untuk periode saat ini dan sebelumnya
        $currentData = $this->fetchData($startDate, $endDate, $brandId, $marketPlaceId);
        $previousData = $this->fetchData($previousStartDate, $previousEndDate, $brandId, $marketPlaceId);

        // Menghitung persentase perubahan
        $changes = $this->calculatePercentageChanges($currentData, $previousData);

        // Menyusun respons
        $summary = [
            'current' => $currentData,
            'previous' => $previousData,
            'changes' => $changes,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'brand_id' => $brandId,
            'market_place_id' => $marketPlaceId
        ];

        return response()->json($summary);
    }

    private function fetchData($startDate, $endDate, $brandId, $marketPlaceId)
    {
        $query = MetaCpasData::selectRaw(
            'SUM(amount_spent) as total_amount_spent,
             SUM(content_views_with_shared_items) as total_content_views,
             SUM(adds_to_cart_with_shared_items) as total_adds_to_cart,
             SUM(purchases_with_shared_items) as total_purchases,
             SUM(purchases_conversion_value_for_shared_items_only) as total_purchases_conversion_value,
             SUM(impressions) as total_impressions'
        )->whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($brandId && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        if ($marketPlaceId && $marketPlaceId != 0) {
            $query->where('market_place_id', $marketPlaceId);
        }

        $result = $query->first();

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
        return $changes;
    }

    public function latestRetrievedAt()
    {
        $latestData = MetaCpasData::orderBy('retrieved_at', 'desc')->first();
        return $latestData ? $latestData->retrieved_at : 'No data available';
    }
}
