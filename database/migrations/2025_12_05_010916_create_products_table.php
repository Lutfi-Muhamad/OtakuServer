<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // relasi ke store (boleh null)
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->integer('price')->nullable();
            $table->integer('stock');

            $table->string('folder')->nullable();
            $table->string('image_key')->nullable();

            $table->string('image_type')->default('square');
            $table->string('aspect_ratio')->default('1:1');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
