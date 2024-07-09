<?php

namespace App\Http\Controllers;

use App\Models\ShopeeSellerCenterCoinData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShopeeSellerCenterCoinDataController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $query = ShopeeSellerCenterCoinData::select([
            'id',
            'data_date',
            'time',
            'name',
            'coins_amount',
            'brand_id',
        ])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $data = $query->get();

        // Grouping data by 'data_date' and aggregating coins_amount
        $groupedData = $data->groupBy('data_date')->map(function ($dateGroup) {
            return [
                'data_date' => $dateGroup->first()->data_date,
                'total_coins_amount' => $dateGroup->sum('coins_amount'),
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
}
