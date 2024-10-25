<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOdooSoDataTable extends Migration
{

    public function up()
    {
        Schema::table('odoo_so_data', function (Blueprint $table) {
            $table->bigInteger('ol_brand_id')->nullable()->after('ol_price_total');
            $table->text('ol_brand_name')->nullable()->after('ol_brand_id');
            $table->bigInteger('ol_product_identifier_id')->nullable()->after('ol_brand_name');
            $table->text('ol_product_identifier_name')->nullable()->after('ol_product_identifier_id');
            $table->bigInteger('ol_market_id')->nullable()->after('ol_product_identifier_name');
            $table->text('ol_market_name')->nullable()->after('ol_market_id');
            $table->bigInteger('ol_city_id')->nullable()->after('ol_market_name');
            $table->text('ol_city_name')->nullable()->after('ol_city_id');
            $table->bigInteger('ol_province_id')->nullable()->after('ol_city_name');
            $table->text('ol_province_name')->nullable()->after('ol_province_id');
        });
    }

    public function down()
    {
        Schema::table('odoo_so_data', function (Blueprint $table) {
            $table->dropColumn([
                'ol_brand_id',
                'ol_brand_name',
                'ol_product_identifier_id',
                'ol_product_identifier_name',
                'ol_market_id',
                'ol_market_name',
                'ol_city_id',
                'ol_city_name',
                'ol_province_id',
                'ol_province_name'
            ]);
        });
    }
}
