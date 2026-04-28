<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Cek stok cukup
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Stok tidak mencukupi'
            ], 422);
        }

        // Hitung total harga
        $totalPrice = $product->surplus_price * $request->quantity;

        // Buat order, user_id dari token login
        $order = Order::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'status' => 'Diproses',
        ]);

        // Kurangi stok otomatis
        $product->decrement('stock', $request->quantity);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat',
            'order' => $order->load('product')
        ], 201);
    }

    // Ambil pesanan milik user yang sedang login
    public function myOrders(Request $request)
    {
        $orders = Order::with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }
}
