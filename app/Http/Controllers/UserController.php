<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/user  — data user yang sedang login (sudah ada di Sanctum default)
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // PATCH /api/user/location  — update koordinat + shop_name
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'sometimes|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui.',
            'data' => $request->user()->fresh(),
        ]);
    }
}
