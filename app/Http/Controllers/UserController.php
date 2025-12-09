<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

public function updateProfile(Request $request)
{
$user = auth()->user();

if ($request->hasFile('photo')) {
$baseName = strtolower(str_replace(' ', '-', $user->name));
$existing = User::where('photo', 'like', "$baseName-%")->count() + 1;

$filename = $baseName . '-' . $existing . '.' . $request->photo->extension();

$path = $request->photo->storeAs(
'public/user',
$filename
);

$user->photo = $filename;
}

$user->bio = $request->bio;
$user->address = $request->address;
$user->save();

return response()->json([
'status' => true,
'message' => 'Profil berhasil diperbarui',
'user' => $user
]);
}