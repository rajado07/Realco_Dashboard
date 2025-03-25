<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiktok_video_analytics_data', function (Blueprint $table) {
            $table->decimal('ctr', 8, 2)->nullable()->change();
            $table->decimal('v_to_l_rate', 8, 2)->nullable()->change();
            $table->decimal('video_finish_rate', 8, 2)->nullable()->change();
            $table->decimal('ctor', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tiktok_video_analytics_data', function (Blueprint $table) {
            $table->decimal('ctr', 5, 2)->nullable()->change();
            $table->decimal('v_to_l_rate', 5, 2)->nullable()->change();
            $table->decimal('video_finish_rate', 5, 2)->nullable()->change();
            $table->decimal('ctor', 5, 2)->nullable()->change();
        });
    }
};