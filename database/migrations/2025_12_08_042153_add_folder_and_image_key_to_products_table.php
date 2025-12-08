<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('folder')->nullable()->after('stock');
            $table->string('image_key')->nullable()->after('folder');

            // Hapus kolom image lama JIKA ADA
            if (Schema::hasColumn('products', 'image')) {
                $table->dropColumn('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->dropColumn(['folder', 'image_key']);
        });
    }
};
