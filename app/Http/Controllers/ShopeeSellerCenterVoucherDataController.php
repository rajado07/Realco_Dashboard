<?php

namespace App\Http\Controllers;

use App\Models\ShopeeSellerCenterVoucherData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShopeeSellerCenterVoucherDataController extends Controller
{

    public function index(Request $request)
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $brandId = $request->input('brand_id', null);

        $query = ShopeeSellerCenterVoucherData::whereBetween('data_date', [$startDate, $endDate]);

        if (!is_null($brandId) && $brandId != 0) {
            $query->where('brand_id', $brandId);
        }

        $vouchers = $query->get()->groupBy('voucher_name')->map(function ($items, $voucherName) {
            // Mendapatkan item pertama untuk mendapat brand_id sebagai representatif
            $brandId = $items->first()->brand_id;

            return [
                'voucher_name' => $voucherName,
                'total_claims' => $items->sum('claim'),
                'total_orders' => $items->sum('order'),
                'total_sales' => $items->sum('sales'),
                'total_costs' => $items->sum('cost'),
                'total_units_sold' => $items->sum('units_sold'),
                'total_buyers' => $items->sum('buyers'),
                'average_sales_per_buyer' => $items->avg('sales_per_buyer'),
                'average_roi' => $items->avg('roi'),
                'brand_id' => $brandId,
                'details' => $items->map(function ($item) {
                    // Menyertakan semua data yang relevan untuk setiap voucher dalam detail
                    return [
                        'id' => $item->id,
                        'data_date' => $item->data_date,
                        'voucher_code' => $item->voucher_code,
                        'claim_start' => $item->claim_start,
                        'claim_end' => $item->claim_end,
                        'voucher_type' => $item->voucher_type,
                        'claim' => $item->claim,
                        'order' => $item->order,
                        'sales' => $item->sales,
                        'cost' => $item->cost,
                        'units_sold' => $item->units_sold,
                        'buyers' => $item->buyers,
                        'sales_per_buyer' => $item->sales_per_buyer,
                        'roi' => $item->roi,
                        'brand_id' => $item->brand_id,
                    ];
                })->values()->all()
            ];
        })->values()->all();

        return response()->json($vouchers);
    }
}
