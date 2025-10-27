<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('meta_ctwa_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); // Day

            $table->string('ad_set_name');
            $table->unsignedBigInteger('ad_set_id');
            $table->string('ad_name')->nullable();
            $table->integer('amount_spent')->nullable();
            $table->integer('messaging_conversations_started')->nullable();
            $table->integer('cost_per_messaging_conversation_started')->nullable();
            $table->integer('purchases')->nullable();
            $table->integer('purchases_conversion_value')->nullable();

            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('market_place_id'); 
            $table->unsignedBigInteger('raw_data_id'); 
            $table->unsignedBigInteger('data_group_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ctwa_data');
    }
};
