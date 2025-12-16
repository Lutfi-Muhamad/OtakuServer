<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReport;

class ReportController extends Controller
{
    public function totalSales(Request $request, $storeId)
    {
        // 🔍 DEBUG BACKEND
        logger('TOTAL SALES HIT', [
            'store_id' => $storeId,
            'user_id'  => $request->user()->id,
        ]);

        $reports = SalesReport::where('store_id', $storeId)
            ->orderByDesc('total_sold')
            ->get([
                'product_name',
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
