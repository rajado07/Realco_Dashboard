<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('odoo_target_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date')->nullable(); 
            $table->string('odoo_user')->nullable(); 
            $table->integer('target')->nullable(); 
            $table->string('type')->nullable(); 
            $table->unsignedBigInteger('brand_id'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_target_data');
    }
};
