<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // ============================
    // GET CART USER
    // ============================
    public function index()
    {
        $carts = Cart::with('product')
            ->where('user_id', auth()->id())
            ->get();

        $carts->map(function ($cart) {

            $folder = $cart->product->folder;
            $key = $cart->product->image_key;

            // ✅ Generate 1 gambar utama untuk cart
            $cart->product->image = asset("storage/products/$folder/{$key}-01.jpg");



            return $cart;
        });

        return response()->json([
            'status' => true,
            'carts' => $carts
        ]);
    }

    // ============================
    // ADD TO CART
    // ============================
    public function store(Request $request)
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            $cart->increment('qty');
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'qty' => $request->qty ?? 1
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Produk berhasil ditambahkan ke cart'
        ]);
    }

    // ============================
    // UPDATE QTY
    // ============================
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->qty = $request->qty;
        $cart->save();

        return response()->json([
            'status' => true,
            'message' => 'Qty berhasil diupdate'
        ]);
    }

    // ============================
    // DELETE CART
    // ============================
    public function destroy($id)
    {
        Cart::destroy($id);

        return response()->json([
            'status' => true,
            'message' => 'Cart berhasil dihapus'
        ]);
    }
}
