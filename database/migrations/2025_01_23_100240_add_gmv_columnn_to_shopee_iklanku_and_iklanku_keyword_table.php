<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shopee_seller_center_iklanku_data', function (Blueprint $table) {
            $table->integer('gmv')->nullable()->after('items_sold'); # Omzet Penjualan
        });

        Schema::table('shopee_seller_center_iklanku_keyword_data', function (Blueprint $table) {
            $table->integer('gmv')->nullable()->after('items_sold'); # Omzet Penjualan
        });
    }

    public function down()
    {
        Schema::table('shopee_seller_center_iklanku_data', function (Blueprint $table) {
            $table->dropColumn('gmv');
        });

        Schema::table('shopee_seller_center_iklanku_keyword_data', function (Blueprint $table) {
            $table->dropColumn('gmv');
        });
    }
};
