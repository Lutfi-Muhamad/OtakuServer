<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class ProductController extends Controller
{
    // =========================
    // GET IMAGES PRODUCT
    // =========================
    private function getProductImages($product)
    {
        $folderPath = storage_path("app/public/products/{$product->folder}");
        $images = [];

        if (File::exists($folderPath)) {
            $files = File::files($folderPath);

            foreach ($files as $file) {
                $filename = strtolower($file->getFilename());

                if (str_contains($filename, strtolower($product->image_key))) {
                    $images[] = asset("storage/products/{$product->folder}/" . $file->getFilename());
                }
            }
        }

        return $images;
    }

    // =========================
    // LIST PRODUCT
    // =========================
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

    // =========================
    // PRODUCT DETAIL
    // =========================
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $product->images = $this->getProductImages($product);

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }

    // =========================
    // SEARCH
    // =========================
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

    // =========================
    // STORE PRODUCT
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "description" => "required|string",
            "price" => "required|numeric",
            "stock" => "required|numeric",
            "folder" => "required|string",
            "images.*" => "image|mimes:jpg,jpeg,png|max:5120",
        ]);

        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json([
                "status" => false,
                "message" => "User does not have a store."
            ], 403);
        }

        $cleanName = preg_replace('/[^a-z0-9\-]+/', '', str_replace(' ', '-', strtolower($request->name)));
        $cleanName = preg_replace('/-+/', '-', $cleanName);
        $imageKey = trim($cleanName, '-');
        $folder = strtolower($request->folder);

        // buat folder jika belum ada
        $storagePath = storage_path("app/public/products/{$folder}");
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $product = Product::create([
            "name" => $request->name,
            "description" => $request->description,
            "price" => $request->price,
            "stock" => $request->stock,
            "image_type" => "square",
            "aspect_ratio" => "1:1",
            "folder" => $folder,
            "image_key" => $imageKey,
            "store_id" => $store->id
        ]);

        // =========================
        // UPLOAD IMAGES DENGAN DEBUG
        // =========================
        if ($request->hasFile('images')) {
            $files = $request->file('images');

            // pastikan $files selalu array
            $files = is_array($files) ? $files : [$files];

            $storagePath = storage_path("app/public/products/" . strtolower($request->folder));
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $i = 1;
            foreach ($files as $file) {
                $filename = $imageKey . "-" . str_pad($i, 2, "0", STR_PAD_LEFT) . "." . $file->getClientOriginalExtension();
                Log::info('📂 SAVING TO: ' . $storagePath . '/' . $filename);

                $file->move($storagePath, $filename);  // <- pakai move
                $i++;
            }
        } else {
            Log::info('📥 NO FILES RECEIVED');
        }



        return response()->json([
            "status" => true,
            "message" => "Product created successfully.",
            "product" => $product
        ]);
    }


    // =========================
    // EDIT PRODUCT (SELLER)
    // =========================
    public function editProduk(Request $request, $storeId, $productId)
    {
        logger('🟨 [EDIT PRODUK API]');
        logger('🏪 storeId = ' . $storeId);
        logger('📦 productId = ' . $productId);
        logger('📥 payload', $request->all());

        $user = auth()->user();
        $store = $user->store; // 🔥 BENAR

        // 1️⃣ Validasi user punya store
        if (!$store) {
            logger('❌ USER HAS NO STORE');
            return response()->json(['message' => 'User has no store'], 403);
        }

        // 2️⃣ Validasi store ID
        if ($store->id != $storeId) {
            logger('❌ STORE ID MISMATCH', [
                'user_store_id' => $store->id,
                'request_store_id' => $storeId,
            ]);

            return response()->json(['message' => 'Unauthorized store'], 403);
        }

        // 3️⃣ Ambil produk milik store
        $product = Product::where('id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if (!$product) {
            logger('❌ PRODUCT NOT FOUND');
            return response()->json(['message' => 'Product not found'], 404);
        }

        // 4️⃣ Update produk
        $product->update([
            'name'        => $request->name,
            'price'       => $request->price,
            'description' => $request->description,
        ]);

        logger('✅ PRODUCT UPDATED');

        return response()->json([
            'message' => 'Product updated',
            'product' => $product,
        ]);
    }

    // AMBIL PRODUK BERDASARKAN TOKO
    public function byStore($storeId)
    {
        $products = Product::where('store_id', $storeId)->get();

        foreach ($products as $product) {
            $product->images = $this->getProductImages($product);
        }

        return response()->json([
            'status' => true,
            'products' => $products
        ]);
    }


    // =========================
    // DELETE PRODUCT
    // =========================
    public function destroy($id)
    {
        $user = Auth::user();
        $store = $user->store;

        Log::info('🧨 DELETE REQUEST', [
            'product_id' => $id,
            'user_id' => $user?->id,
            'store_id' => $store?->id,
        ]);

        if (!$store) {
            Log::warning('❌ USER HAS NO STORE');
            return response()->json(['message' => 'User has no store'], 403);
        }

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->first();

        if (!$product) {
            Log::warning('❌ PRODUCT NOT FOUND OR NOT OWNED', [
                'store_id' => $store->id,
            ]);
            return response()->json(['message' => 'Product not found'], 404);
        }

        // hapus gambar produk spesifik (tidak seluruh folder)
        $folderPath = storage_path("app/public/products/{$product->folder}");

        if (File::exists($folderPath)) {
            $files = File::files($folderPath);

            foreach ($files as $file) {
                if (str_contains(strtolower($file->getFilename()), strtolower($product->image_key))) {
                    File::delete($file);
                }
            }

            Log::info('🗑️ PRODUCT IMAGES DELETED', [
                'product_id' => $product->id,
                'folder' => $folderPath,
            ]);
        }

        // hapus produk dari database
        $product->delete();

        Log::info('✅ PRODUCT DELETED', [
            'product_id' => $id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product deleted'
        ]);
    }
}
