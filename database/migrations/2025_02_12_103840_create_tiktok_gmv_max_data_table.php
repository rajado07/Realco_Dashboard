<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiktok_gmv_max_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date');
            $table->unsignedBigInteger('campaign_id'); // Campaign ID
            $table->string('campaign_name')->nullable(); // Campaign name
            $table->integer('cost')->nullable(); // Cost
            $table->integer('orders')->nullable(); // Orders (SKU) 
            $table->integer('gross_revenue')->nullable(); // Gross revenue

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
        Schema::dropIfExists('tiktok_gmv_max_data');
    }
};
