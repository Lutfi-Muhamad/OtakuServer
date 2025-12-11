<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Store;


class StoreController extends Controller
{
    public function registerStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah user sudah punya store
        if ($user->store_id) {
            return response()->json([
                'status' => false,
                'message' => 'Kamu tidak bisa memiliki lebih dari 1 toko.'
            ], 400);
        }

        // Buat toko
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Update user → store_id
        $user->update([
            'store_id' => $store->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Toko berhasil didaftarkan!',
            'store' => $store
        ]);
    }
}
