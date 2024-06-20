<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('meta_cpas_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Day
            $table->string('ad_set_name');
            $table->unsignedBigInteger('ad_set_id');
            $table->integer('amount_spent')->nullable();
            $table->integer('content_views_with_shared_items')->nullable();
            $table->integer('adds_to_cart_with_shared_items')->nullable();
            $table->integer('purchases_with_shared_items')->nullable();
            $table->integer('purchases_conversion_value_for_shared_items_only')->nullable();
            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('market_place_id'); 
            $table->unsignedBigInteger('raw_data_id');  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_cpas_data');
    }
};
