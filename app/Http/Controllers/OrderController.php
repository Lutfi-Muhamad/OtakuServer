<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // GET /api/orders
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil order milik user, urutkan dari yang terbaru
        // Sertakan 'items' agar detail barang muncul
        $orders = Order::where('user_id', $user->id)
                    ->with('items') 
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }
}
