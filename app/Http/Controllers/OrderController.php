<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try{
            $order = Order::create([
                'product_id' => $request->product_id,
                'total_price' => $request->total_price,
                'customer_name' => $request->costumer_name ?? 'UMKM',
            ]);

            return response()->json([
                'message' => 'Sukses!',
                'order' => $order
            ], 201);
        } catch (\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
