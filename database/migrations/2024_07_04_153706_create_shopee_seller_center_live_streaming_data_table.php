<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopee_seller_center_live_streaming_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->string('name');
            $table->time('start_at');
            $table->integer('duration');
            $table->integer('unique_viewers')->nullable();
            $table->integer('peak_viewers')->nullable();
            $table->integer('avg_watch_time')->nullable();
            $table->integer('orders')->nullable();
            $table->bigInteger('sales')->nullable();
            
            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');
            $table->unsignedBigInteger('data_group_id')->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_seller_center_live_streaming_data');
    }
};
