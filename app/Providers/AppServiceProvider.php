<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RawData;
use App\Observers\ShopeeBrandPortalShopDataObserver;
use App\Observers\ShopeeBrandPortalAdsDataObserver;
use App\Observers\MetaCpasDataObserver;


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
        RawData::observe(MetaCpasDataObserver::class);
    }
}
