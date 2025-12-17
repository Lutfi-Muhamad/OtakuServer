<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesReport;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        $storeId = 1;

        $products = Product::whereNotNull('price')->get();

        $categories = ['nendroid', 'bags', 'figure'];

        foreach ($products as $product) {

            // jumlah transaksi per produk
            $transactions = rand(5, 15);

            for ($i = 0; $i < $transactions; $i++) {

                // tentukan rentang waktu
                $period = rand(1, 3);

                if ($period === 1) {
                    // minggu ini
                    $soldAt = Carbon::now()->subDays(rand(0, 6));
                } elseif ($period === 2) {
                    // 1 bulan lalu
                    $soldAt = Carbon::now()->subMonth()->addDays(rand(0, 27));
                } else {
                    // 2 bulan lalu
                    $soldAt = Carbon::now()->subMonths(2)->addDays(rand(0, 27));
                }

                $totalSold = rand(1, 10);
                $totalRevenue = $totalSold * $product->price;

                SalesReport::create([
                    'store_id'      => $storeId,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'category'      => $categories[array_rand($categories)],
                    'series'        => $product->folder,
                    'total_sold'    => $totalSold,
                    'total_revenue' => $totalRevenue,
                    'sold_at'       => $soldAt,
                ]);
            }
        }
    }
}
