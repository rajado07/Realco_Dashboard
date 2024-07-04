<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiktok_lsa_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Date

            $table->string('ad_group_name');
            $table->unsignedBigInteger('ad_group_id');
            $table->string('ad_name')->nullable();
            $table->integer('cost')->nullable();
            $table->integer('live_views')->nullable();
            $table->integer('live_unique_views')->nullable();
            $table->integer('effective_live_views')->nullable();
            $table->integer('purchases')->nullable();
            $table->integer('gross_revenue')->nullable();
            $table->integer('impressions')->nullable();
            
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
        Schema::dropIfExists('tiktok_lsa_data');
    }
};
