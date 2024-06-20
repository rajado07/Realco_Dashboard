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
        Schema::create('tiktok_vsa_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Date
            $table->string('ad_group_name');
            $table->unsignedBigInteger('ad_group_id');
            $table->integer('cost')->nullable();
            $table->float('average_watch_time_per_video_view')->nullable();
            $table->integer('adds_to_cart')->nullable();
            $table->integer('purchases')->nullable();
            $table->integer('gross_revenue')->nullable();
            $table->integer('checkouts_initiated')->nullable();
            $table->integer('product_page_views')->nullable();
            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_vsa_data');
    }
};
