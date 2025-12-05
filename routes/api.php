<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;

// =======================
// AUTH (PUBLIC)
// =======================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =======================
// PRODUK (PUBLIC)
// =======================
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);

// =======================
// PROTECTED (SANCTUM)
// =======================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart', [CartController::class, 'store']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'bearer' => $request->bearerToken(),
        'user' => $request->user(),
    ]);
})->middleware('auth:sanctum');
