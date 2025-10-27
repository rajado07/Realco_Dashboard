<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_generators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('market_place_id');
            $table->string('type');
            $table->text('link');
            $table->string('frequency'); // daily, weekly, hourly, etc.
            $table->time('run_at'); // waktu eksekusi dalam sehari
            $table->integer('status')->default('1'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_generators');
    }
};
