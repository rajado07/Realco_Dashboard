<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('shopee_seller_center_iklanku_data', function (Blueprint $table) {
            $table->id();
            $table->date('data_date'); 

            $table->string('name')->nullable(); # Nama Iklan
            $table->string('status')->nullable(); # Status
            $table->string('ad_type')->nullable(); # Jenis Iklan
            $table->string('product_code')->nullable(); # Kode Produk
            $table->string('display_type')->nullable(); # Tampilan Iklan
            $table->string('bidding_type')->nullable(); # Mode Bidding
            $table->string('ad_placement')->nullable(); # Penempatan Iklan
            $table->string('start_date')->nullable(); # Tanggal Mulai
            $table->string('end_date')->nullable(); # Tanggal Selesai
            $table->integer('impressions')->nullable(); # Dilihat
            $table->integer('clicks')->nullable(); # Jumlah Klik
            $table->integer('items_sold')->nullable(); # Produk Terjual
            $table->integer('expense')->nullable(); # Biaya
            $table->decimal('roas', 10, 2)->nullable(); # Efektifitas Iklan
            $table->decimal('acos', 10, 2)->nullable(); # Persentase Biaya Iklan terhadap Penjualan dari Iklan (ACOS)

            $table->timestamp('retrieved_at'); 
            $table->string('file_name');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('raw_data_id');
            $table->unsignedBigInteger('data_group_id')->nullable();  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopee_seller_center_iklanku_data');
    }
};
