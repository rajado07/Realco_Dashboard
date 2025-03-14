<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('brand_target_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date')->nullable();  
            $table->string('sub_brand_name')->nullable(); 
            $table->integer('target_nmv')->nullable(); 
            $table->integer('target_ads_to_nmv')->nullable(); 
            $table->integer('composition_cpas')->nullable(); 
            $table->integer('composition_iklanku')->nullable(); 
            $table->unsignedBigInteger('brand_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_target_data');
    }
};
