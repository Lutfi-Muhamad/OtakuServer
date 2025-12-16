<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesReport;
use Illuminate\Database\Seeder;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [1];

        $products = Product::all();

        $categories = ['nendroid', 'bags', 'figure'];

        foreach ($stores as $storeId) {
            foreach ($products as $product) {

                if (is_null($product->price)) {
                    continue;
                }

                $totalSold = rand(10, 150);
                $totalRevenue = $totalSold * $product->price;

                SalesReport::create([
                    'store_id'      => $storeId,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'category'      => $categories[array_rand($categories)],
                    'series'        => $product->folder, // onepiece, jjk, dll
                    'total_sold'    => $totalSold,
                    'total_revenue' => $totalRevenue,
                ]);
            }
        }
    }
}
