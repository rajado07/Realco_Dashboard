<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('market_place_id');
            $table->string('type');
            $table->text('link');
            $table->timestamp('scheduled_to_run'); // waktu dijadwalkan untuk dijalankan
            $table->integer('status')->default('1'); 
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
