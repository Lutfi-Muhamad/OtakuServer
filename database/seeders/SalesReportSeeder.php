<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesReport;
use Illuminate\Database\Seeder;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        // toko dummy
        $stores = [1];

        // ambil semua produk
        $products = Product::all();

        foreach ($stores as $storeId) {
            foreach ($products as $product) {

                // skip produk tanpa harga (misal banner / promo)
                if (is_null($product->price)) {
                    continue;
                }

                // dummy data random tapi masuk akal
                $totalSold = rand(10, 150);
                $totalRevenue = $totalSold * $product->price;

                SalesReport::create([
                    'store_id'      => $storeId,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'series'        => $product->folder, // onepiece, jjk, dll
                    'total_sold'    => $totalSold,
                    'total_revenue' => $totalRevenue,
                ]);
            }
        }
    }
}
