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
            $table->string('product_name');
            $table->string('product_id');
            $table->decimal('gross_sales', 10, 2)->nullable();
            $table->integer('gross_orders')->nullable();
            $table->integer('gross_units_sold')->nullable();
            $table->integer('product_views')->nullable();
            $table->integer('product_visitors')->nullable();
            $table->timestamp('retrieved_at'); 
            $table->date('data_date'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_brand_portal_shop_data');
    }
    
};
