<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingColumnsToOdooSoDataTable extends Migration
{

    public function up()
    {
        Schema::table('odoo_so_data', function (Blueprint $table) {
            $table->bigInteger('partner_shipping_city_id')->nullable()->after('odoo_partner_cust_rank_name');
            $table->text('partner_shipping_city')->nullable()->after('partner_shipping_city_id');
            $table->bigInteger('partner_shipping_province_id')->nullable()->after('partner_shipping_city');
            $table->text('partner_shipping_province')->nullable()->after('partner_shipping_province_id');
        });
    }

    public function down()
    {
        Schema::table('odoo_so_data', function (Blueprint $table) {
            $table->dropColumn('partner_shipping_city_id');
            $table->dropColumn('partner_shipping_city');
            $table->dropColumn('partner_shipping_province_id');
            $table->dropColumn('partner_shipping_province');
        });
    }
}
