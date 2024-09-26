<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RawData;
use App\Observers\RawDataStatusObserver;
use App\Observers\ShopeeBrandPortalShopDataObserver;
use App\Observers\ShopeeBrandPortalAdsDataObserver;
use App\Observers\ShopeeSellerCenterLiveStreamingDataObserver;
use App\Observers\ShopeeSellerCenterVoucherDataObserver;
use App\Observers\ShopeeSellerCenterCoinDataObserver;
use App\Observers\MetaCpasDataObserver;
use App\Observers\TiktokPsaDataObserver;
use App\Observers\TiktokLsaDataObserver;
use App\Observers\TiktokVsaDataObserver;
use App\Observers\TiktokGmvDataObserver;
use App\Observers\TiktokProductAnalyticsDataObserver;
use App\Observers\TiktokPromotionsAnalyticsDataObserver;
use App\Observers\TiktokVideoAnalyticsDataObserver;
use App\Observers\TiktokLiveStreamingDataObserver;


class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
        //
    }

    public function boot()
    {
        RawData::observe(RawDataStatusObserver::class);
        RawData::observe(ShopeeBrandPortalShopDataObserver::class);
        RawData::observe(ShopeeBrandPortalAdsDataObserver::class);
        RawData::observe(ShopeeSellerCenterLiveStreamingDataObserver::class);
        RawData::observe(ShopeeSellerCenterVoucherDataObserver::class);
        RawData::observe(ShopeeSellerCenterCoinDataObserver::class);
        RawData::observe(MetaCpasDataObserver::class);
        RawData::observe(TiktokPsaDataObserver::class);
        RawData::observe(TiktokLsaDataObserver::class);
        RawData::observe(TiktokVsaDataObserver::class);
        RawData::observe(TiktokGmvDataObserver::class);
        RawData::observe(TiktokProductAnalyticsDataObserver::class);
        RawData::observe(TiktokPromotionsAnalyticsDataObserver::class);
        RawData::observe(TiktokVideoAnalyticsDataObserver::class);
        RawData::observe(TiktokLiveStreamingDataObserver::class);
    }
}
