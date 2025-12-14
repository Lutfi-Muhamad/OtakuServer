<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use Illuminate\Http\Request;

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
            $files = is_array($files) ? $files : [$files];

            Log::info('📥 RECEIVED IMAGES COUNT: ' . count($files));

            $i = 1;
            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension();
                $filename = $imageKey . "-" . str_pad($i, 2, "0", STR_PAD_LEFT) . "." . $ext;

                Log::info('📥 FILE NAME: ' . $file->getClientOriginalName());
                Log::info('📂 WILL BE SAVED TO: ' . $storagePath . '/' . $filename);

                // simpan file
                $file->move($storagePath, $filename);
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
    // UPDATE PRODUCT
    // =========================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            "name" => "string",
            "description" => "string",
            "price" => "numeric",
            "stock" => "numeric",
            "folder" => "string",
            "images.*" => "image|mimes:jpg,jpeg,png|max:5120",
        ]);

        // Update data produk
        $product->update($request->only([
            "name",
            "description",
            "price",
            "stock",
            "folder"
        ]));

        // Jika ganti folder → hapus semua gambar lama
        if ($request->folder) {
            $oldFolder = storage_path("app/public/products/" . $product->folder);
            if (File::exists($oldFolder)) {
                File::deleteDirectory($oldFolder);
            }
        }

        // =========================
        // Upload gambar baru
        // =========================
        if ($request->hasFile("images")) {
            $cleanName = str_replace(" ", "-", strtolower($product->name));
            $product->image_key = $cleanName;
            $product->save();

            $i = 1;

            foreach ($request->file("images") as $img) {
                $filename = $cleanName . "-" . str_pad($i, 2, "0", STR_PAD_LEFT) . "." . $img->getClientOriginalExtension();

                $img->storeAs(
                    "public/products/" . strtolower($product->folder),
                    $filename
                );

                $i++;
            }
        }

        return response()->json([
            "status" => true,
            "message" => "Product updated successfully.",
            "product" => $product
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
        $product = Product::findOrFail($id);

        $folderPath = storage_path("app/public/products/{$product->folder}");

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $product->delete();

        return response()->json([
            "status" => true,
            "message" => "Product deleted successfully."
        ]);
    }
}
