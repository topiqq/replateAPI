<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// ── PUBLIC ────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/foods', [ProductController::class, 'index']);

// ── AUTHENTICATED (semua role) ────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());

    // Orders — wajib login
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/mine', [OrderController::class, 'myOrders']);
});

// ── PARTNER ONLY ──────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:partner'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/mine', [ProductController::class, 'myProducts']);
});

// ── ADMIN ONLY ────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/stats', [AdminController::class, 'getDashboardStats']);
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
