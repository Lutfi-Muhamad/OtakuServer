<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;



class CartController extends Controller
{
    // ============================
    // GET CART USER
    // ============================
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        $carts->map(function ($cart) {
            if ($cart->product) {
                $folder = $cart->product->folder;
                $key = $cart->product->image_key;

                $cart->product->image =
                    asset("storage/products/$folder/{$key}-01.jpg");
            }

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
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        // 🔎 DEBUG LOG
        Log::info('🛒 ADD TO CART', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_store_id' => $product->store_id,
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $cart->increment('qty', $request->qty ?? 1);
        } else {
            Cart::create([
                'user_id'    => $user->id,
                'store_id'   => $product->store_id, // ✅ FIXED
                'product_id' => $product->id,
                'qty'        => $request->qty ?? 1,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Produk berhasil ditambahkan ke cart'
        ], 201);
    }

    // ============================
    // UPDATE QTY
    // ============================
    public function update(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

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

    // CHECK OUT
    public function checkout(Request $request)
    {
        $user = Auth::user();

        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Cart kosong'], 400);
        }

        DB::transaction(function () use ($carts, $user) {

            // asumsi 1 toko
            $storeId = $carts->first()->store_id;

            $total = 0;

            foreach ($carts as $cart) {
                $total += $cart->product->price * $cart->qty;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'store_id' => $storeId,
                'total_price' => $total,
                'status' => 'completed', // sementara langsung selesai
            ]);

            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'qty' => $cart->qty,
                    'price' => $cart->product->price,
                ]);
            }

            // cart dibersihkan
            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json(['message' => 'Checkout berhasil']);
    }
}
