<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // LIST PRODUK
    public function index()
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product->image = asset('storage/' . $product->image);
        }

        return response()->json([
            'status' => true,
            'products' => Product::all()
        ]);
    }

    // DETAIL PRODUK
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->image = asset('storage/' . $product->image);

        return response()->json([
            'status' => true,
            'product' => Product::findOrFail($id)
        ]);
    }

    // SEARCH PRODUK
    public function search(Request $request)
    {
        $data = Product::where('name', 'like', '%' . $request->q . '%')->get();

        return response()->json([
            'status' => true,
            'products' => $data
        ]);
    }
}
