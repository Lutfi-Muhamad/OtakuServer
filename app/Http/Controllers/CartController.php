<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // TAMBAH KE CART
    public function store(Request $request)
    {
        // ✅ AMBIL USER LANGSUNG DARI REQUEST (SANCTUM)
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User belum login'
            ], 401);
        }


        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);

        Cart::create([
            'user_id' => $user->id,         
            'product_id' => $request->product_id,
            'qty' => $request->qty
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Produk ditambahkan ke cart'
        ]);
    }

    // LIHAT CART
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'cart' => Cart::with('product')
                ->where('user_id', $user->id)
                ->get()
        ]);
    }
}
