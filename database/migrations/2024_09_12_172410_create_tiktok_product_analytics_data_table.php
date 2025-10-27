<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiktokProductAnalyticsDataTable extends Migration
{

    public function up()
    {
        Schema::create('tiktok_product_analytics_data', function (Blueprint $table) {
            $table->id(); // ID
            $table->date('data_date'); // Tanggal
            $table->unsignedBigInteger('product_id');
            $table->string('product_name')->nullable(); 
            $table->string('status')->nullable(); 
            
            // Shop Tab
            $table->unsignedBigInteger('gmv')->nullable(); // GMV
            $table->unsignedInteger('units_sold')->nullable(); // Units sold
            $table->unsignedInteger('orders')->nullable(); // Orders
            $table->unsignedBigInteger('shop_tab_gmv')->nullable(); // Shop tab GMV
            $table->unsignedInteger('shop_tab_units_sold')->nullable(); // Shop Tab units sold
            $table->unsignedInteger('shop_tab_listing_impressions')->nullable(); // Shop tab listing impressions
            $table->unsignedInteger('shop_tab_page_views')->nullable(); // Shop tab page views
            $table->unsignedInteger('shop_tab_unique_page_views')->nullable(); // Shop tab unique page views
            $table->unsignedInteger('shop_tab_unique_product_buyers')->nullable(); // Shop tab unique product buyers
            $table->decimal('shop_tab_clickthrough_rate', 5, 2)->nullable(); // Shop tab clickthrough rate
            $table->decimal('shop_tab_conversion_rate', 5, 2)->nullable(); // Shop tab conversion rate
            
            // LIVE
            $table->unsignedBigInteger('live_gmv')->nullable(); // LIVE GMV
            $table->unsignedInteger('live_units_sold')->nullable(); // LIVE units sold
            $table->unsignedInteger('live_impressions')->nullable(); // LIVE impressions
            $table->unsignedInteger('page_views_from_live')->nullable(); // Page views from LIVE
            $table->unsignedInteger('unique_page_views_from_live')->nullable(); // Unique page views from LIVE
            $table->unsignedInteger('live_unique_product_buyers')->nullable(); // LIVE unique product buyers
            $table->decimal('live_clickthrough_rate', 5, 2)->nullable(); // LIVE click-through rate
            $table->decimal('live_conversion_rate', 5, 2)->nullable(); // LIVE conversion rate
            
            // Video
            $table->unsignedBigInteger('video_gmv')->nullable(); // Video GMV
            $table->unsignedInteger('video_units_sold')->nullable(); // Video units sold
            $table->unsignedInteger('video_impressions')->nullable(); // Video impressions
            $table->unsignedInteger('page_views_from_video')->nullable(); // Page views from video
            $table->unsignedInteger('unique_page_views_from_video')->nullable(); // Unique page views from video
            $table->unsignedInteger('video_unique_product_buyers')->nullable(); // Video unique product buyers
            $table->decimal('video_clickthrough_rate', 5, 2)->nullable(); // Video click-through rate
            $table->decimal('video_conversion_rate', 5, 2)->nullable(); // Video conversion rate
            
            // Product Card
            $table->unsignedBigInteger('product_card_gmv')->nullable(); // Product card GMV
            $table->unsignedInteger('product_card_units_sold')->nullable(); // Product card units sold
            $table->unsignedInteger('product_card_impressions')->nullable(); // Product card impressions
            $table->unsignedInteger('page_views_from_product_card')->nullable(); // Page views from product card
            $table->unsignedInteger('unique_page_views_from_product_card')->nullable(); // Unique page views from product card
            $table->unsignedInteger('product_card_unique_buyers')->nullable(); // Product card unique buyers
            $table->decimal('product_card_clickthrough_rate', 5, 2)->nullable(); // Product card click-through rate
            $table->decimal('product_card_conversion_rate', 5, 2)->nullable(); // Product card conversion rate
            
            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');
            $table->unsignedBigInteger('data_group_id')->nullable();  
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('tiktok_product_analytics_data');
    }
}
