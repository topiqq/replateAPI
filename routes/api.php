<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/partner', [AuthController::class, 'registerPartner']);
Route::post('/register/buyer', [AuthController::class, 'registerBuyer']);

// Katalog produk bisa dilihat tanpa login
Route::get('/products', [ProductController::class, 'catalog']);

// ── Protected routes ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'me']);
    Route::patch('/user/location', [UserController::class, 'updateLocation']);

    // ── Partner & Admin ───────────────────────────────────
    Route::middleware('role:partner,admin')->group(function () {
        // Manajemen produk sendiri
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/mine', [ProductController::class, 'index']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Pesanan masuk ke toko partner
        Route::get('orders/incoming', [OrderController::class, 'incoming']);
        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
    });

    // ── Buyer ─────────────────────────────────────────────
    Route::middleware('role:buyer')->group(function () {
        // Buat pesanan
        Route::post('orders', [OrderController::class, 'store']);
        // Riwayat pesanan sendiri
        Route::get('orders/my', [OrderController::class, 'myOrders']);
        // Detail pesanan
        Route::get('orders/{id}', [OrderController::class, 'show']);
        // Upload verifikasi identitas
        Route::post('user/verification', [UserController::class, 'uploadVerification']);
        // Impact dashboard personal
        Route::get('user/impact', [UserController::class, 'impact']);
    });

    // ── Admin only ────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Semua user
        Route::get('admin/users', [UserController::class, 'index']);
        Route::put('admin/users/{id}/role', [UserController::class, 'updateRole']);
        Route::put('admin/users/{id}/verify', [UserController::class, 'verify']);

        // Semua transaksi
        Route::get('admin/orders', [OrderController::class, 'index']);

        // Moderasi produk
        Route::delete('admin/products/{id}', [ProductController::class, 'adminDestroy']);
        Route::put('admin/products/{id}', [ProductController::class, 'adminUpdate']);

        // City-wide impact
        Route::get('admin/impact', [UserController::class, 'cityImpact']);
    });
});
