<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TiktokLsaData;

class TiktokLsaDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $query = TiktokLsaData::select([
            'id',
            'data_date',
            'ad_set_id',
            'ad_set_name',
            'amount_spent',
            'content_views_with_shared_items',
            'adds_to_cart_with_shared_items',
            'purchases_with_shared_items',
            'purchases_conversion_value_for_shared_items_only',
            'brand_id',
            'market_place_id',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $data = $query->get();

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
        $query = TiktokLsaData::selectRaw(
            'SUM(amount_spent) as total_amount_spent,
             SUM(content_views_with_shared_items) as total_content_views,
             SUM(adds_to_cart_with_shared_items) as total_adds_to_cart,
             SUM(purchases_with_shared_items) as total_purchases,
             SUM(purchases_conversion_value_for_shared_items_only) as total_purchases_conversion_value'
        )->whereBetween('data_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($brandId && $brandId != 0) {
            $query->where('brand_id', $brandId);
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
        $latestData = TiktokLsaData::orderBy('retrieved_at', 'desc')->first();
        return $latestData ? $latestData->retrieved_at : 'No data available';
    }
}
