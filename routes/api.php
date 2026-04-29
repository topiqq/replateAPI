<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Public routes (tidak perlu token) ─────────
Route::post('/login',  [AuthController::class, 'login']);

// ── Protected routes (perlu Sanctum token) ────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [UserController::class, 'me']);

    // Produk — hanya Partner & Admin
    Route::middleware('role:partner,admin')->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    // Pesanan — hanya Partner & Admin
    Route::middleware('role:partner,admin')->group(function () {
        Route::get('orders',        [OrderController::class, 'index']);
        Route::put('orders/{id}',   [OrderController::class, 'update']);
    });

    // Update lokasi toko
    Route::patch('/user/location', [UserController::class, 'updateLocation'])
         ->middleware('role:partner,admin');
});
