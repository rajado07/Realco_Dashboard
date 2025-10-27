<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('raw_data', function (Blueprint $table) {
            $table->id();
            $table->string('type'); 
            $table->longText('data'); 
            $table->timestamp('retrieved_at'); 
            $table->date('data_date'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id'); 
            $table->unsignedBigInteger('market_place_id'); 
            $table->unsignedBigInteger('task_id'); 
            $table->integer('status')->default('1'); 
            $table->longText('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_data');
    }
};
