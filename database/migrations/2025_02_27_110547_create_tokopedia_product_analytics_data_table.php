<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tokopedia_product_analytics_data', function (Blueprint $table) {
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

            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');
            $table->unsignedBigInteger('data_group_id')->nullable();  
            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tokopedia_product_analytics_data');
    }
};
