<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReport;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;


class ReportController extends Controller
{
    public function sales(Request $request, $storeId)
    {
        $category  = $request->query('category');
        $series    = $request->query('series');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = SalesReport::where('store_id', $storeId);

        if ($category) {
            $query->where('category', $category);
        }

        if ($series) {
            $query->where('series', $series);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('sold_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        }

        $reports = $query
            ->selectRaw('DATE(sold_at) as date')
            ->selectRaw('CAST(SUM(total_sold) AS UNSIGNED) as total_sold')
            ->selectRaw('CAST(SUM(total_revenue) AS UNSIGNED) as total_revenue')
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'date'          => $row->date,
                    'total_sold'    => (int) $row->total_sold,
                    'total_revenue' => (int) $row->total_revenue,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $reports,
        ]);
    }

    public function productSales(Request $request, $storeId)
    {
        $order = $request->query('order');       // top | bottom
        $category = $request->query('category'); // nendroid | bags | figure
        $series = $request->query('series');     // onepiece | jjk

        $query = DB::table('sales_reports')
            ->where('store_id', $storeId)
            ->selectRaw('
                product_id,
                product_name,
                category,
                series,
                SUM(total_sold) as total_sold,
                SUM(total_revenue) as total_revenue
            ')
            ->groupBy(
                'product_id',
                'product_name',
                'category',
                'series'
            );

        if ($category) {
            $query->where('category', $category);
        }

        if ($series) {
            $query->where('series', $series);
        }

        if ($order === 'top') {
            $query->orderByDesc('total_sold');
        } elseif ($order === 'bottom') {
            $query->orderBy('total_sold');
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }
}
