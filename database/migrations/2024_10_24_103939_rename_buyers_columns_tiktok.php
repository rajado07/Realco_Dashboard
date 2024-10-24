<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBuyersColumnsTiktok extends Migration
{

    public function up()
    {
        // Rename column in tiktok_gmv_data
        Schema::table('tiktok_gmv_data', function (Blueprint $table) {
            $table->renameColumn('buyers', 'customers');
        });

        // Rename columns in tiktok_promotion_analytics_data
        Schema::table('tiktok_promotion_analytics_data', function (Blueprint $table) {
            $table->renameColumn('buyers', 'customers');
            $table->renameColumn('new_buyers', 'new_customers');
        });

        // Rename column in tiktok_video_analytics_data
        Schema::table('tiktok_video_analytics_data', function (Blueprint $table) {
            $table->renameColumn('buyers', 'customers');
        });
    }

    public function down()
    {
        // Revert column name changes in tiktok_gmv_data
        Schema::table('tiktok_gmv_data', function (Blueprint $table) {
            $table->renameColumn('customers', 'buyers');
        });

        // Revert column name changes in tiktok_promotion_analytics_data
        Schema::table('tiktok_promotion_analytics_data', function (Blueprint $table) {
            $table->renameColumn('customers', 'buyers');
            $table->renameColumn('new_customers', 'new_buyers');
        });

        // Revert column name change in tiktok_video_analytics_data
        Schema::table('tiktok_video_analytics_data', function (Blueprint $table) {
            $table->renameColumn('customers', 'buyers');
        });
    }
}
