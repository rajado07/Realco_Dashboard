<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RawData;
use App\Models\ImportData;
use App\Observers\RawDataStatusObserver;
use App\Observers\ShopeeBrandPortalShopDataObserver;
use App\Observers\ShopeeBrandPortalAdsDataObserver;
use App\Observers\ShopeeSellerCenterLiveStreamingDataObserver;
use App\Observers\ShopeeSellerCenterVoucherDataObserver;
use App\Observers\ShopeeSellerCenterCoinDataObserver;
use App\Observers\ShopeeSellerCenterIklankuDataObserver;
use App\Observers\ShopeeSellerCenterIklankuKeywordDataObserver;
use App\Observers\MetaCpasDataObserver;
use App\Observers\MetaCtwaDataObserver;
use App\Observers\OdooSoDataObserver;
use App\Observers\TiktokPsaDataObserver;
use App\Observers\TiktokLsaDataObserver;
use App\Observers\TiktokVsaDataObserver;
use App\Observers\TiktokGmvDataObserver;
use App\Observers\TiktokGmvMaxDataObserver;
use App\Observers\TiktokProductAnalyticsDataObserver;
use App\Observers\TiktokPromotionsAnalyticsDataObserver;
use App\Observers\TiktokVideoAnalyticsDataObserver;
use App\Observers\TiktokLiveStreamingDataObserver;
use App\Observers\TokopediaGmvDataObserver;
use App\Observers\TokopediaPromotionsAnalyticsDataObserver;
use App\Observers\TokopediaProductAnalyticsDataObserver;

use App\Observers\OdooTargetDataObserver;
use App\Observers\BrandTargetDataObserver;
use App\Observers\FsBoostingDataObserver;


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
        RawData::observe(ShopeeSellerCenterIklankuDataObserver::class);
        RawData::observe(ShopeeSellerCenterIklankuKeywordDataObserver::class);
        RawData::observe(MetaCpasDataObserver::class);
        RawData::observe(MetaCtwaDataObserver::class);
        RawData::observe(OdooSoDataObserver::class);
        RawData::observe(TiktokPsaDataObserver::class);
        RawData::observe(TiktokLsaDataObserver::class);
        RawData::observe(TiktokVsaDataObserver::class);
        RawData::observe(TiktokGmvDataObserver::class);
        RawData::observe(TiktokGmvMaxDataObserver::class);
        RawData::observe(TiktokProductAnalyticsDataObserver::class);
        RawData::observe(TiktokPromotionsAnalyticsDataObserver::class);
        RawData::observe(TiktokVideoAnalyticsDataObserver::class);
        RawData::observe(TiktokLiveStreamingDataObserver::class);
        RawData::observe(TokopediaGmvDataObserver::class);
        RawData::observe(TokopediaPromotionsAnalyticsDataObserver::class);
        RawData::observe(TokopediaProductAnalyticsDataObserver::class);

        ImportData::observe(OdooTargetDataObserver::class);
        ImportData::observe(BrandTargetDataObserver::class);
        ImportData::observe(FsBoostingDataObserver::class);
        
    }
}
