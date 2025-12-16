<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_reports', function (Blueprint $table) {
            $table->id();

            // RELASI TOKO
            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            // RELASI PRODUK
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // SNAPSHOT DATA PRODUK
            $table->string('product_name');
            $table->string('series')->nullable(); // dari folder (onepiece, jjk, dll)

            // DATA LAPORAN
            $table->integer('total_sold')->default(0);      // pcs terjual
            $table->bigInteger('total_revenue')->default(0); // total uang

            $table->timestamps();

            // OPTIONAL INDEX (biar query cepat)
            $table->index(['store_id', 'product_id']);
            $table->index('series');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_reports');
    }
};
