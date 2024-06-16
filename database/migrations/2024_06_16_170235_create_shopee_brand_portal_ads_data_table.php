<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('shopee_brand_portal_ads_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Date
            $table->string('shop_name');
            $table->integer('shop_id');
            $table->integer('impressions')->nullable();
            $table->integer('orders')->nullable();
            $table->integer('gross_sales')->nullable();
            $table->integer('ads_spend')->nullable();
            $table->integer('units_sold')->nullable();
            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_brand_portal_ads_data');
    }
};
