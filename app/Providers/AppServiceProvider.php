<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RawData;
use App\Observers\ShopeeBrandPortalShopDataObserver;
use App\Observers\ShopeeBrandPortalAdsDataObserver;
use App\Observers\ShopeeSellerCenterLiveStreamingDataObserver;
use App\Observers\ShopeeSellerCenterVoucherDataObserver;
use App\Observers\ShopeeSellerCenterCoinDataObserver;
use App\Observers\MetaCpasDataObserver;
use App\Observers\TiktokPsaDataObserver;
use App\Observers\TiktokLsaDataObserver;
use App\Observers\TiktokVsaDataObserver;



class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
        //
    }

    public function boot()
    {
        RawData::observe(ShopeeBrandPortalShopDataObserver::class);
        RawData::observe(ShopeeBrandPortalAdsDataObserver::class);
        RawData::observe(ShopeeSellerCenterLiveStreamingDataObserver::class);
        RawData::observe(ShopeeSellerCenterVoucherDataObserver::class);
        RawData::observe(ShopeeSellerCenterCoinDataObserver::class);
        RawData::observe(MetaCpasDataObserver::class);
        RawData::observe(TiktokPsaDataObserver::class);
        RawData::observe(TiktokLsaDataObserver::class);
        RawData::observe(TiktokVsaDataObserver::class);
    }
}
