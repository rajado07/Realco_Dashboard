<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tokopedia_gmv_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Tanggal

            $table->unsignedInteger('gmv')->nullable();
            $table->unsignedInteger('refunds')->nullable();
            $table->unsignedInteger('gross_revenue')->nullable();
            $table->unsignedInteger('items_sold')->nullable();
            $table->unsignedInteger('customers')->nullable();
            $table->unsignedInteger('page_views')->nullable();
            $table->unsignedInteger('visitors')->nullable();
            $table->unsignedInteger('sku_orders')->nullable();
            $table->unsignedInteger('orders')->nullable();
            $table->decimal('conversion_rate', 5, 2)->nullable();

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
        Schema::dropIfExists('tokopedia_gmv_data');
    }
};
