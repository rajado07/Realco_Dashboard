<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiktok_video_analytics_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Tanggal

            $table->string('creator_name')->nullable();
            $table->bigInteger('creator_id')->nullable();
            $table->text('video_info')->nullable();
            $table->bigInteger('video_id')->nullable();
            $table->timestamp('time')->nullable();
            $table->text('products')->nullable();
            $table->integer('vv')->nullable();
            $table->integer('likes')->nullable();
            $table->integer('comments')->nullable();
            $table->integer('shares')->nullable();
            $table->integer('new_followers')->nullable();
            $table->integer('v_to_l_clicks')->nullable();
            $table->integer('product_impressions')->nullable();
            $table->integer('product_clicks')->nullable();
            $table->integer('buyers')->nullable();
            $table->integer('orders')->nullable();
            $table->integer('unit_sales')->nullable();
            $table->bigInteger('video_revenue')->nullable();
            $table->bigInteger('gpm')->nullable();
            $table->bigInteger('shoppable_video_attributed_gmv')->nullable();
            $table->decimal('ctr', 5, 2)->nullable();
            $table->decimal('v_to_l_rate', 5, 2)->nullable();
            $table->decimal('video_finish_rate', 5, 2)->nullable();
            $table->decimal('ctor', 5, 2)->nullable();

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
        Schema::dropIfExists('tiktok_video_analytics_data');
    }
};
