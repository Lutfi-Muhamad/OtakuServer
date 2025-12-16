<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReport;

class ReportController extends Controller
{

    // TOtalsales
    public function sales(Request $request, $storeId)
    {
        $order = $request->query('order', 'top'); // default top
        $category = $request->query('category');  // nullable
        $series = $request->query('series');      // nullable

        $query = SalesReport::where('store_id', $storeId);

        // filter category
        if ($category) {
            $query->where('category', $category);
        }

        // filter series
        if ($series) {
            $query->where('series', $series);
        }

        // sorting
        if ($order === 'bottom') {
            $query->orderBy('total_sold', 'asc');
        } else {
            $query->orderBy('total_sold', 'desc');
        }

        $reports = $query->get([
            'product_name',
            'category',
            'series',
            'total_sold',
            'total_revenue',
        ]);

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }
}
