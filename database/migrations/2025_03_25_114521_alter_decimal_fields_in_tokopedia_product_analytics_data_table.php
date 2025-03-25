<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokopedia_product_analytics_data', function (Blueprint $table) {
            $table->decimal('shop_tab_clickthrough_rate', 8, 2)->nullable()->change();
            $table->decimal('shop_tab_conversion_rate', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tokopedia_product_analytics_data', function (Blueprint $table) {
            $table->decimal('shop_tab_clickthrough_rate', 5, 2)->nullable()->change();
            $table->decimal('shop_tab_conversion_rate', 5, 2)->nullable()->change();
        });
    }
};
