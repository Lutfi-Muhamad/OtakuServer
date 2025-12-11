<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user(); // auth()->user()

        // VALIDASI INPUT
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
        ]);

        // UPDATE FIELD DASAR
        $user->name = $request->name;
        $user->bio = $request->bio;
        $user->address = $request->address;

        // HANDLE FOTO UPLOAD
        if ($request->hasFile('photo')) {

            // DELETE OLD PHOTO IF EXISTS
            if ($user->photo && Storage::exists('public/user/' . $user->photo)) {
                Storage::delete('public/user/' . $user->photo);
            }

            // GENERATE FILE NAME
            $baseName = strtolower(str_replace(' ', '-', $user->name));
            $filename = $baseName . '-' . time() . '.' . $request->photo->extension();

            // simpan file
            $request->photo->storeAs('user', $filename, 'public');


            $user->photo = $filename;
        }

        // SIMPAN KE DATABASE
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'bio'     => $user->bio,
                'address' => $user->address,
                'photo'   => $user->photo
                    ? asset('storage/user/' . $user->photo)
                    : null,
            ]
        ]);
    }

    // SHOW PROFILE

    public function showProfile(Request $request)
    {
        $user = $request->user(); // auth()->user()

        return response()->json([
            'status' => true,
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'bio'       => $user->bio,
                'address'   => $user->address,
                'photo'     => $user->photo,
                'store_id'     => $user->store_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }
    
}
