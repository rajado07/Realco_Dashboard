<?php

namespace App\Http\Controllers;

use App\Models\ShopeeBrandPortalShopData;
use App\Models\ShopeeBrandPortalAdsData;
use App\Models\ShopeeSellerCenterCoinData;
use App\Models\ShopeeSellerCenterLiveStreamingData;
use App\Models\ShopeeSellerCenterVoucherData;

use App\Models\TiktokPsaData;
use App\Models\TiktokLsaData;
use App\Models\TiktokVsaData;

use App\Models\TiktokGmvData;
use App\Models\TiktokLiveStreamingData;
use App\Models\TiktokProductAnalyticsData;
use App\Models\TiktokPromotionAnalyticsData;
use App\Models\TiktokVideoAnalyticsData;

use App\Models\MetaCpasData;
use App\Models\Brand;
use App\Models\MarketPlace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;


class DataCheckerController extends Controller
{
    public function checkDataDates(Request $request)
    {
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()->toDateString()))->toDateString(); // Default: hari ini
        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonth()->toDateString()))->toDateString(); // Default: sebulan ke belakang

        // Log::info('Start Date: ' . $startDate);
        // Log::info('End Date: ' . $endDate);

        $models = [
            'shopee_brand_portal_shop' => ShopeeBrandPortalShopData::class,
            'shopee_brand_portal_ads' => ShopeeBrandPortalAdsData::class,
            'shopee_seller_center_coins' => ShopeeSellerCenterCoinData::class,
            'shopee_seller_center_live_streaming' => ShopeeSellerCenterLiveStreamingData::class,
            'shopee_seller_center_voucher' => ShopeeSellerCenterVoucherData::class,
            'meta_cpas' => MetaCpasData::class,
            'tiktok_psa' => TiktokPsaData::class,
            'tiktok_lsa' => TiktokLsaData::class,
            'tiktok_vsa' => TiktokVsaData::class,
        ];

        $missingDatesData = [];
        $counter = 1;

        $brands = Brand::all();
        $marketPlaces = MarketPlace::all();

        foreach ($models as $type => $model) {
            $tableName = (new $model)->getTable();
            $hasMarketPlaceId = Schema::hasColumn($tableName, 'market_place_id');

            foreach ($brands as $brand) {
                if ($hasMarketPlaceId) {
                    foreach ($marketPlaces as $marketPlace) {
                        // Ambil tanggal dari database untuk setiap brand dan market_place_id
                        $existingDates = $model::where('brand_id', $brand->id)
                            ->where('market_place_id', $marketPlace->id)
                            ->whereBetween('data_date', [$startDate, $endDate])
                            ->distinct()
                            ->pluck('data_date')
                            ->map(function ($date) {
                                return Carbon::parse($date)->toDateString();
                            });

                        // Buat daftar semua tanggal antara startDate dan endDate
                        $allDates = [];
                        for ($date = Carbon::parse($startDate); $date->lte($endDate); $date->addDay()) {
                            $allDates[] = $date->toDateString();
                        }

                        // Cari tanggal yang tidak ada di database
                        $missingDates = array_diff($allDates, $existingDates->toArray());

                        // Tambahkan hasil ke output
                        foreach ($missingDates as $missingDate) {
                            $missingDatesData[] = [
                                'no' => $counter++,
                                'type' => $type,
                                'missing_date' => $missingDate,
                                'brand_id' => $brand->id,
                                'market_place_id' => $marketPlace->id,
                            ];
                        }
                    }
                } else {
                    // Cek tanpa market_place_id
                    $existingDates = $model::where('brand_id', $brand->id)
                        ->whereBetween('data_date', [$startDate, $endDate])
                        ->distinct()
                        ->pluck('data_date')
                        ->map(function ($date) {
                            return Carbon::parse($date)->toDateString();
                        });

                    // Buat daftar semua tanggal antara startDate dan endDate
                    $allDates = [];
                    for ($date = Carbon::parse($startDate); $date->lte($endDate); $date->addDay()) {
                        $allDates[] = $date->toDateString();
                    }

                    // Cari tanggal yang tidak ada di database
                    $missingDates = array_diff($allDates, $existingDates->toArray());

                    // Tambahkan hasil ke output
                    foreach ($missingDates as $missingDate) {
                        $missingDatesData[] = [
                            'no' => $counter++,
                            'type' => $type,
                            'missing_date' => $missingDate,
                            'brand_id' => $brand->id,
                            'market_place_id' => null,
                        ];
                    }
                }
            }
        }

        // Kembalikan hasil dalam bentuk JSON
        return response()->json($missingDatesData);
    }
}
