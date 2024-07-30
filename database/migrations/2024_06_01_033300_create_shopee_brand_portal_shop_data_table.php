<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('shopee_brand_portal_shop_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->string('product_name');
            $table->string('product_id');
            $table->decimal('gross_sales', 15, 2)->nullable();
            $table->integer('gross_orders')->nullable();
            $table->integer('gross_units_sold')->nullable();
            $table->integer('product_views')->nullable();
            $table->integer('product_visitors')->nullable();

            $table->double('avg_basket_size')->nullable();
            $table->double('avg_selling_price')->nullable();
            
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
        Schema::dropIfExists('shopee_brand_portal_shop_data');
    }
    
};
