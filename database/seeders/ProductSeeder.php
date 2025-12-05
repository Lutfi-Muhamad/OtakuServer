<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Produk kotak (square)
        Product::create([
            'name' => "Gojo Satoru - Nendoroid",
            'description' => "Figure Gojo skala kecil",
            'price' => 500000,
            'stock' => 20,
            'image' => "products/Onepiece/OnePiece-Figure-01.jpg",
            'image_type' => 'square',
            'aspect_ratio' => '1:1'
        ]);

        Product::create([
            'name' => "Nami - Nendoroid",
            'description' => "Figure Nami edisi spesial",
            'price' => 350000,
            'stock' => 15,
            'image' => "products/Onepiece/OnePiece-Figure-02.jpg",
            'image_type' => 'square',
            'aspect_ratio' => '1:1'
        ]);

        // Banner wide (trending)
        Product::create([
            'name' => "One Piece Banner 01",
            'description' => "Banner konten trending",
            'price' => null,
            'stock' => 0,
            'image' => "products/Onepiece/OnePiece-Banner-01.jpg",
            'image_type' => 'wide',
            'aspect_ratio' => '16:9'
        ]);

        // Promo unik (contoh Luffy 50%)
        Product::create([
            'name' => "Luffy Promo 50%",
            'description' => "Promo spesial karakter Luffy",
            'price' => null,
            'stock' => 0,
            'image' => "products/Onepiece/OnePiece-Promo-01.jpg",
            'image_type' => 'promo',
            'aspect_ratio' => '16:9'
        ]);
    }
}
