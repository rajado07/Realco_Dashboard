<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiktok_promotion_analytics_data', function (Blueprint $table) {
            $table->id(); // ID
            $table->date('data_date'); // Tanggal

            $table->string('promotion_name')->nullable();
            $table->string('promotion_period')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->decimal('ctor', 5, 2)->nullable();
            $table->unsignedBigInteger('gmv')->nullable();
            $table->integer('orders')->nullable();
            $table->integer('buyers')->nullable();
            $table->integer('products_sold')->nullable();
            $table->integer('new_buyers')->nullable();
            $table->unsignedBigInteger('avg_gmv_per_buyer')->nullable();
            $table->unsignedBigInteger('discount_amount')->nullable();
            $table->decimal('avg_discount_rate', 5, 2)->nullable();
            $table->decimal('roi', 5, 2)->nullable();

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
        Schema::dropIfExists('tiktok_promotion_analytics_data');
    }
};
