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
        Schema::create('data_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->longText('id_mapping')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('market_place_id'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_groups');
    }
};
