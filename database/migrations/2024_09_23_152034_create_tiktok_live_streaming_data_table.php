<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiktok_live_streaming_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->bigInteger('creator_id')->nullable();
            $table->string('creator_name')->nullable();
            $table->string('nickname')->nullable();
            $table->timestamp('launched_time')->nullable();
            $table->integer('duration')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->integer('products')->nullable();
            $table->integer('different_products_sold')->nullable();
            $table->integer('orders_created')->nullable();
            $table->integer('orders_paid')->nullable();
            $table->integer('unit_sales')->nullable();
            $table->integer('buyers')->nullable();
            $table->bigInteger('average_price')->nullable();
            $table->decimal('co_rate', 5, 2)->nullable();
            $table->bigInteger('live_attributed_gmv')->nullable();
            $table->integer('viewers')->nullable();
            $table->integer('views')->nullable();
            $table->integer('avg_viewing_duration')->nullable();
            $table->integer('comments')->nullable();
            $table->integer('shares')->nullable();
            $table->integer('likes')->nullable();
            $table->integer('new_followers')->nullable();
            $table->integer('product_impressions')->nullable();
            $table->integer('product_clicks')->nullable();
            $table->decimal('ctr', 5, 2)->nullable();

            $table->string('type')->nullable();
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
        Schema::dropIfExists('tiktok_live_streaming_data');
    }
};
