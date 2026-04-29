<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // GET /api/orders
    // Partner melihat semua pesanan untuk produk yang dimiliki toko-nya
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with('product')
            ->whereHas('product', function ($q) use ($user) {
                $q->where('seller', $user->shop_name);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $orders]);
    }

    // PUT /api/orders/{id}  — update status saja
    public function update(Request $request, $id)
    {
        $order = Order::with('product')->findOrFail($id);

        // Pastikan pesanan ini memang untuk produk milik Partner yang login
        if ($order->product->seller !== $request->user()->shop_name) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Diproses,Selesai,Dibatalkan',
        ]);

        $order->update($validated);

        return response()->json(['data' => $order->fresh('product')]);
    }
}
