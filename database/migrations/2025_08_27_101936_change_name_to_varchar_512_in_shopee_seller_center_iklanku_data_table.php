<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopee_seller_center_iklanku_data', function (Blueprint $table) {
            $table->string('name', 512)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shopee_seller_center_iklanku_data', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->change();
        });
    }
};
