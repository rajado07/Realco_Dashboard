<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('user_data_dir')->nullable()->after('name');
            $table->string('profile_dir')->nullable()->after('user_data_dir');
            $table->string('download_directory')->nullable()->after('profile_dir');
            $table->string('fast_api_url')->nullable()->after('download_directory');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['user_data_dir', 'profile_dir','download_directory', 'fast_api_url']);
        });
    }
};
