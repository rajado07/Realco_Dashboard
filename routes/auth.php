<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DataCheckerController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DataGroupController;
use App\Http\Controllers\MarketPlaceController;
use App\Http\Controllers\RawDataController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskGeneratorController;
use App\Http\Controllers\ShopeeBrandPortalShopDataController;
use App\Http\Controllers\ShopeeBrandPortalAdsDataController;
use App\Http\Controllers\ShopeeSellerCenterLiveStreamingDataController;
use App\Http\Controllers\ShopeeSellerCenterCoinDataController;
use App\Http\Controllers\ShopeeSellerCenterVoucherDataController;
use App\Http\Controllers\ShopeeSummaryDataController;
use App\Http\Controllers\MetaCpasDataController;
use App\Http\Controllers\OdooTargetDataController;
use App\Http\Controllers\BrandTargetDataController;

use Illuminate\Support\Facades\Route;

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');



// DataChecker
Route::post('/data-checker/check-dates', [DataCheckerController::class, 'checkDataDates'])->middleware('auth');

// Log
Route::get('/logs/read', [LogController::class, 'index'])->middleware('auth');

// RawData
Route::get('/raw-data/read', [RawDataController::class, 'index'])->middleware('auth');
Route::get('/raw-data/status-count', [RawDataController::class, 'getRawDataStatusCount'])->middleware('auth');

// ImportData
Route::get('/import/read', [ImportDataController::class, 'index'])->middleware('auth');
Route::post('/import/data', [ImportDataController::class, 'import'])->middleware('auth');

// Brand
Route::get('/brand/read', [BrandController::class, 'index'])->middleware('auth');
Route::post('/brand/store', [BrandController::class, 'store'])->middleware('auth');
Route::delete('/brand/destroy/{id}', [BrandController::class, 'destroy'])->middleware('auth');
Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->middleware('auth');
Route::post('/brand/update', [BrandController::class, 'update'])->middleware('auth');

// Market Place
Route::get('/market-place/read', [MarketPlaceController::class, 'index'])->middleware('auth');
Route::post('/market-place/store', [MarketPlaceController::class, 'store'])->middleware('auth');
Route::delete('/market-place/destroy/{id}', [MarketPlaceController::class, 'destroy'])->middleware('auth');
Route::get('/market-place/edit/{id}', [MarketPlaceController::class, 'edit'])->middleware('auth');
Route::post('/market-place/update', [MarketPlaceController::class, 'update'])->middleware('auth');

// Group
Route::get('/group/read', [DataGroupController::class, 'index'])->middleware('auth');
Route::post('/group/store', [DataGroupController::class, 'store'])->middleware('auth');
Route::delete('/group/destroy/{id}', [DataGroupController::class, 'destroy'])->middleware('auth');
Route::get('/group/edit/{id}', [DataGroupController::class, 'edit'])->middleware('auth');
Route::post('/group/update', [DataGroupController::class, 'update'])->middleware('auth');
Route::get('/group/type', [DataGroupController::class, 'getDataGroupType'])->middleware('auth');
Route::get('/group/bytype/{type?}', [DataGroupController::class, 'getDataGroupByType'])->middleware('auth');

// Task
Route::get('/task/read', [TaskController::class, 'index'])->middleware('auth');
Route::get('/task/read/{type}', [TaskController::class, 'getTaskByType'])->middleware('auth');
Route::get('/task/status-count', [TaskController::class, 'getTaskStatusCount'])->middleware('auth');
Route::post('/task/update/status', [TaskController::class, 'updateStatus'])->middleware('auth');
Route::post('/task/exception-details', [TaskController::class, 'getExceptionDetails'])->middleware('auth');

// TaskGenerator
Route::get('/task-generator/read', [TaskGeneratorController::class, 'index'])->middleware('auth');
Route::get('/task-generator/get-script', [TaskGeneratorController::class, 'getScript'])->middleware('auth');
Route::get('/task-generator/edit/{id}', [TaskGeneratorController::class, 'edit'])->middleware('auth');
Route::delete('/task-generator/destroy/{id}', [TaskGeneratorController::class, 'destroy'])->middleware('auth');
Route::post('/task-generator/store', [TaskGeneratorController::class, 'store'])->middleware('auth');
Route::post('/task-generator/update', [TaskGeneratorController::class, 'update'])->middleware('auth');
Route::post('/task-generator/generate', [TaskGeneratorController::class, 'generateTask'])->middleware('auth');

//Shopee-BrandPortal-Shop
Route::get('/shopee/brand-portal-shop/read', [ShopeeBrandPortalShopDataController::class, 'index'])->middleware('auth');
Route::get('/shopee/brand-portal-shop/summary', [ShopeeBrandPortalShopDataController::class, 'getSummary'])->middleware('auth');
Route::get('/shopee/brand-portal-shop/get-data-by-group', [ShopeeBrandPortalShopDataController::class, 'showDataByGroup'])->middleware('auth');
Route::get('/shopee/brand-portal-shop/latest-data', [ShopeeBrandPortalShopDataController::class, 'latestRetrievedAt'])->middleware('auth');

//Shopee-BrandPortal-Ads
Route::get('/shopee/brand-portal-ads/read', [ShopeeBrandPortalAdsDataController::class, 'index'])->middleware('auth');
Route::get('/shopee/brand-portal-ads/summary', [ShopeeBrandPortalAdsDataController::class, 'getSummary'])->middleware('auth');
Route::get('/shopee/brand-portal-ads/latest-data', [ShopeeBrandPortalAdsDataController::class, 'latestRetrievedAt'])->middleware('auth');

//Shopee-SellerCenter-LiveStreaming
Route::get('/shopee/seller-center-live-streaming/read', [ShopeeSellerCenterLiveStreamingDataController::class, 'index'])->middleware('auth');

//Shopee-SellerCenter-Voucher
Route::get('/shopee/seller-center-voucher/read', [ShopeeSellerCenterVoucherDataController::class, 'index'])->middleware('auth');
Route::get('/shopee/seller-center-voucher/summary', [ShopeeSellerCenterVoucherDataController::class, 'summary'])->middleware('auth');

//Shopee-SellerCenter-Coin
Route::get('/shopee/seller-center-coin/read', [ShopeeSellerCenterCoinDataController::class, 'index'])->middleware('auth');
Route::get('/shopee/seller-center-coin/summary', [ShopeeSellerCenterCoinDataController::class, 'summary'])->middleware('auth');

//Shopee-Summary
Route::get('/shopee/summary/brand-performance/read', [ShopeeSummaryDataController::class, 'shopeeBrand'])->middleware('auth');
Route::get('/shopee/summary/cpas/read', [ShopeeSummaryDataController::class, 'metaCpas'])->middleware('auth');
Route::get('/shopee/summary/ads/read', [ShopeeSummaryDataController::class, 'shopeeAds'])->middleware('auth');
Route::get('/shopee/summary/live-stream/read', [ShopeeSummaryDataController::class, 'shopeeLiveStream'])->middleware('auth');

//Meta-CPAS
Route::get('/meta/cpas/read', [MetaCpasDataController::class, 'index'])->middleware('auth');
Route::get('/meta/cpas/summary', [MetaCpasDataController::class, 'getSummary'])->middleware('auth');
Route::get('/meta/cpas/latest-data', [MetaCpasDataController::class, 'latestRetrievedAt'])->middleware('auth');

// OdooTargetData
Route::get('/target/odoo-target/read', [OdooTargetDataController::class, 'index'])->middleware('auth');
Route::delete('/target/odoo-target/destroy/{id}', [OdooTargetDataController::class, 'destroy'])->middleware('auth');
Route::get('/target/odoo-target/edit/{id}', [OdooTargetDataController::class, 'edit'])->middleware('auth');
Route::post('/target/odoo-target/update', [OdooTargetDataController::class, 'update'])->middleware('auth');

// BrandTargetData
Route::get('/target/brand-target/read', [BrandTargetDataController::class, 'index'])->middleware('auth');
Route::delete('/target/brand-target/destroy/{id}', [BrandTargetDataController::class, 'destroy'])->middleware('auth');
Route::get('/target/brand-target/edit/{id}', [BrandTargetDataController::class, 'edit'])->middleware('auth');
Route::post('/target/brand-target/update', [BrandTargetDataController::class, 'update'])->middleware('auth');