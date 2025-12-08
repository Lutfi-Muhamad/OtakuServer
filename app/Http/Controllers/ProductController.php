<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // =========================
    // AMBIL SEMUA GAMBAR PRODUK
    // =========================
    private function getProductImages($product)
    {
        $folderPath = storage_path("app/public/products/{$product->folder}");
        $images = [];

        if (File::exists($folderPath)) {
            $files = File::files($folderPath);

            foreach ($files as $file) {
                $filename = strtolower($file->getFilename());

                // Cocokkan berdasarkan image_key
                if (str_contains($filename, strtolower($product->image_key))) {
                    $images[] = asset("storage/products/{$product->folder}/" . $file->getFilename());
                }
            }
        }

        return $images;
    }

    // =================
    // LIST PRODUK
    // =================
    public function index()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $product->images = $this->getProductImages($product);
        }

        return response()->json([
            'status' => true,
            'products' => $products
        ]);
    }

    // =================
    // DETAIL PRODUK
    // =================
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->images = $this->getProductImages($product);

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }

    // =================
    // SEARCH PRODUK
    // =================
    public function search(Request $request)
    {
        $data = Product::where('name', 'like', '%' . $request->q . '%')->get();

        foreach ($data as $product) {
            $product->images = $this->getProductImages($product);
        }

        return response()->json([
            'status' => true,
            'products' => $data
        ]);
    }
}
