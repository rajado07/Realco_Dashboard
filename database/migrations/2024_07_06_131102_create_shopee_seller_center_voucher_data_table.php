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
        Schema::create('shopee_seller_center_voucher_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->string('voucher_name');
            $table->string('voucher_code');
            $table->dateTime('claim_start')->nullable();
            $table->dateTime('claim_end')->nullable();
            $table->string('voucher_type')->nullable();
            $table->string('reward_type')->nullable();
            $table->integer('claim')->nullable();
            $table->integer('order')->nullable();
            $table->decimal('usage_rate', 5, 2)->nullable();
            $table->integer('sales')->nullable();
            $table->integer('cost')->nullable();
            $table->integer('units_sold')->nullable();
            $table->integer('buyers')->nullable();
            $table->integer('sales_per_buyer')->nullable();
            $table->double('roi')->nullable();
            
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
        Schema::dropIfExists('shopee_seller_center_voucher_data');
    }
};
