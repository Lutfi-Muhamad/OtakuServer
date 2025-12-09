<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // ONE PIECE PRODUCTS
        // =========================

        Product::create([
            'name' => "Luffy - Nendoroid",
            'description' => "Figure Luffy skala kecil",
            'price' => 500000,
            'stock' => 20,
            'folder' => "onepiece",
            'image_key' => "Luffy-Figure",
            'image_type' => 'square',
            'aspect_ratio' => '1:1'
        ]);

        Product::create([
            'name' => "Nami - Nendoroid",
            'description' => "Figure Nami edisi spesial",
            'price' => 350000,
            'stock' => 15,
            'folder' => "onepiece",
            'image_key' => "Nami-Figure",
            'image_type' => 'square',
            'aspect_ratio' => '1:1'
        ]);

        // =========================
        // JJK PRODUCTS
        // =========================

        Product::create([
            'name' => "Gojo Satoru - Nendoroid",
            'description' => "Figure Gojo skala kecil",
            'price' => 600000,
            'stock' => 18,
            'folder' => "jjk",
            'image_key' => "gojo-figure",
            'image_type' => 'square',
            'aspect_ratio' => '1:1'
        ]);

        // =========================
        // BANNER (WIDE)
        // =========================

        Product::create([
            'name' => "Gojo Anime Banner",
            'description' => "Banner Gojo JJK",
            'price' => null,
            'stock' => 0,
            'folder' => "jjk",
            'image_key' => "banner",
            'image_type' => 'wide',
            'aspect_ratio' => '16:9'
        ]);

        // =========================
        // PROMO
        // =========================

        Product::create([
            'name' => "Luffy Promo 50%",
            'description' => "Promo spesial karakter Luffy",
            'price' => null,
            'stock' => 0,
            'folder' => "onepiece",
            'image_key' => "promo",
            'image_type' => 'promo',
            'aspect_ratio' => '16:9'
        ]);
    }
}
