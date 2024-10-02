<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdIdToTiktokDataTables extends Migration
{
    public function up()
    {
        Schema::table('tiktok_lsa_data', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_id')->after('ad_group_id');
        });

        Schema::table('tiktok_psa_data', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_id')->after('ad_group_id');
        });

        Schema::table('tiktok_vsa_data', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_id')->after('ad_group_id');
        });
    }


    public function down()
    {
        Schema::table('tiktok_lsa_data', function (Blueprint $table) {
            $table->dropColumn('ad_id');
        });

        Schema::table('tiktok_psa_data', function (Blueprint $table) {
            $table->dropColumn('ad_id');
        });

        Schema::table('tiktok_vsa_data', function (Blueprint $table) {
            $table->dropColumn('ad_id');
        });
    }
}
