<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Publik — semua bisa lihat katalog
    public function index()
    {
        $products = Product::with('owner:id,name,shop_name')
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return response()->json($products);
    }

    // Hanya partner — tambah produk baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'original_price' => 'required|integer|min:0',
            'surplus_price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:1',
            'emoji' => 'nullable|string',
            'tag' => 'required|string',
        ]);

        $product = Product::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            // seller diambil dari shop_name partner
            'seller' => $request->user()->shop_name ?? $request->user()->name,
            'original_price' => $request->original_price,
            'surplus_price' => $request->surplus_price,
            'stock' => $request->stock,
            'emoji' => $request->emoji ?? '🍱',
            'tag' => $request->tag,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    // Ambil produk milik partner yang login
    public function myProducts(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($products);
    }
}
