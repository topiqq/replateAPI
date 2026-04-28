<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getDashboardStats()
    {
        return response()->json([
            'total_umkm'     => User::where('role', 'umkm')->count(),
            'total_partner'  => User::where('role', 'partner')->count(),
            'total_orders'   => Order::count(),
            'active_orders'  => Order::where('status', 'Diproses')->count(),
            'total_products' => Product::count(),
            // Total surplus dalam kg (asumsi per item 1kg, bisa disesuaikan)
            'total_surplus_kg' => Product::sum('stock'),
        ]);
    }
}
