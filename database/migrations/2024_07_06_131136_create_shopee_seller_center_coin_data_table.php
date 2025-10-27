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
        Schema::create('shopee_seller_center_coin_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->time('time');
            $table->string('name');
            $table->integer('coins_amount');
            
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
        Schema::dropIfExists('shopee_seller_center_coin_data');
    }
};
