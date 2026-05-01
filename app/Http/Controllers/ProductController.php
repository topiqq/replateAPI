<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    // Partner hanya melihat produk milik toko-nya sendiri (by seller)
    public function index(Request $request)
    {
        $user = $request->user();
        $products = Product::where('seller', $user->shop_name)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $products]);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'original_price' => 'required|integer|min:1',
            'surplus_price' => 'required|integer|min:1|lt:original_price',
            'stock' => 'required|integer|min:0',
            'emoji' => 'required|string|max:10',
            'tag' => 'required|string|max:100',
        ]);

        $validated['seller'] = $request->user()->shop_name;
        $validated['user_id'] = $request->user()->id;

        $product = Product::create($validated);

        return response()->json(['data' => $product], 201);
    }

    // GET /api/products/{id}
    public function show(Request $request, $id)
    {
        $product = $this->findOwned($request, $id);
        return response()->json(['data' => $product]);
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = $this->findOwned($request, $id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'original_price' => 'sometimes|integer|min:1',
            'surplus_price' => 'sometimes|integer|min:1',
            'stock' => 'sometimes|integer|min:0',
            'emoji' => 'sometimes|string|max:10',
            'tag' => 'sometimes|string|max:100',
        ]);

        $product->update($validated);

        return response()->json(['data' => $product->fresh()]);
    }

    // DELETE /api/products/{id}
    public function destroy(Request $request, $id)
    {
        $product = $this->findOwned($request, $id);
        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    // ── Private helper ─────────────────────────
    private function findOwned(Request $request, $id): Product
    {
        $product = Product::findOrFail($id);

        if ($product->seller !== $request->user()->shop_name) {
            abort(403, 'Akses ditolak. Produk ini bukan milik toko Anda.');
        }

        return $product;
    }
}
