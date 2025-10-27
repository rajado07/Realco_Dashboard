<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokopedia_promotion_analytics_data', function (Blueprint $table) {
            $table->decimal('ctor', 8, 2)->nullable()->change();
            $table->decimal('roi', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tokopedia_promotion_analytics_data', function (Blueprint $table) {
            $table->decimal('ctor', 5, 2)->nullable()->change();
            $table->decimal('roi', 5, 2)->nullable()->change();
        });
    }
};