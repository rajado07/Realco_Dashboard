<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_groups', function (Blueprint $table) {
            $table->string('keyword')->nullable()->after('type');
            $table->integer('status')->default(1)->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('data_groups', function (Blueprint $table) {
            $table->dropColumn('keyword');
            $table->dropColumn('status');
        });
    }
};
