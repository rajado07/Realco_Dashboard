<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooSoDataTable extends Migration
{

    public function up()
    {
        Schema::create('odoo_so_data', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('odoo_id')->nullable();
            $table->text('so_number')->nullable();
            $table->unsignedBigInteger('odoo_company_id')->nullable();
            $table->text('odoo_company_name')->nullable();
            $table->timestamp('date_order')->nullable();
            $table->text('state')->nullable();
            $table->bigInteger('amount_total')->nullable();
            $table->unsignedBigInteger('odoo_user_id')->nullable();
            $table->text('odoo_user_name')->nullable();
            $table->text('invoice_status')->nullable();
            $table->text('delivery_status')->nullable();
            $table->text('jubelio_so_no')->nullable();
            $table->text('jubelio_status')->nullable();
            $table->bigInteger('odoo_partner_id')->nullable();
            $table->text('odoo_partner_name')->nullable();
            $table->bigInteger('odoo_channel_id')->nullable();
            $table->text('odoo_channel_name')->nullable();
            $table->bigInteger('team_id')->nullable();
            $table->text('team_name')->nullable();
            $table->text('ns_shipping_info_provider')->nullable();
            $table->bigInteger('amount_untaxed')->nullable();
            $table->bigInteger('amount_tax')->nullable();
            $table->bigInteger('service_fee')->nullable();
            $table->bigInteger('insurance_cost')->nullable();
            $table->bigInteger('nrs_add_disc')->nullable();
            $table->bigInteger('nrs_add_fee')->nullable();
            $table->bigInteger('nrs_add_escrow')->nullable();
            $table->text('source_name')->nullable();
            $table->bigInteger('odoo_partner_cust_rank_id')->nullable();
            $table->text('odoo_partner_cust_rank_name')->nullable();
            $table->bigInteger('ol_id')->nullable();
            $table->text('ol_name')->nullable();
            $table->bigInteger('ol_product_uom_qty')->nullable();
            $table->bigInteger('ol_price_unit')->nullable();
            $table->bigInteger('ol_discount')->nullable();
            $table->bigInteger('ol_discount_fixed')->nullable();
            $table->bigInteger('ol_price_subtotal')->nullable();
            $table->bigInteger('ol_price_total')->nullable();

            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');
            $table->unsignedBigInteger('data_group_id')->nullable();  
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('odoo_so_data');
    }
}