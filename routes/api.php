<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
/*
|--------------------------------------------------------------------------
| PUBLIC AUTH
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PUBLIC PRODUCTS
|--------------------------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (SANCTUM)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // USER
    Route::get('/user', fn(Request $request) => $request->user());
    Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'showProfile']);
    Route::post('/user/update', [UserController::class, 'updateProfile']);

    // CART
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // STORE
    Route::post('/store/register', [StoreController::class, 'registerStore']);
    Route::get('/store/me', [StoreController::class, 'myStore']);

    // AUTH
    Route::post('/logout', [AuthController::class, 'logout']);
});
