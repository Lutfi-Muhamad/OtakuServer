<?php

namespace App\Http\Controllers;

use App\Models\SalesReport;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;


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

        Log::info('🧾 CHECKOUT START', [
            'user_id' => $user->id,
        ]);

        $carts = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($carts as $cart) {
                $product = $cart->product; // ← INI YANG KURANG

                // DEBUG
                Log::info('📦 CHECK PRODUCT', [
                    'product_id' => $product->id,
                    'stock' => $product->stock,
                    'qty' => $cart->qty,
                ]);

                // ❌ CEK STOK
                if ($product->stock < $cart->qty) {
                    throw new \Exception(
                        "Stok {$product->name} tidak cukup"
                    );
                }

                // ✅ KURANGI STOK
                $product->decrement('stock', $cart->qty);

                // ✅ SALES REPORT
                SalesReport::create([
                    'store_id'       => $product->store_id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'category'       => $product->category,
                   'series'         => $product->folder, 
                    'total_sold'     => $cart->qty,
                    'total_revenue'  => $cart->qty * $product->price,
                    'sold_at'        => Carbon::now(),
                ]);

                Log::info('✅ SOLD', [
                    'product_id' => $product->id,
                    'sold' => $cart->qty,
                    'revenue' => $cart->qty * $product->price,
                ]);
            }


            // 🧹 HAPUS CART
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            Log::info('🎉 CHECKOUT SUCCESS', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Checkout sukses'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ CHECKOUT FAILED', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
