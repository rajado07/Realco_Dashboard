<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RawData;
use App\Observers\ShopeeBrandPortalShopObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RawData::observe(ShopeeBrandPortalShopObserver::class);
    }
}
