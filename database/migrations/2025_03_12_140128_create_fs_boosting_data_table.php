<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('fs_boosting_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date')->nullable();  
            $table->integer('fs_boosting')->nullable();  
            $table->unsignedBigInteger('brand_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fs_boosting_data');
    }
};
