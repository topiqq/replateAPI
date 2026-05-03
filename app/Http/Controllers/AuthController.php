<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Register Partner (Restoran) ───────────────────────
    // POST /api/register/partner
    public function registerPartner(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shop_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'shop_name' => $validated['shop_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'partner',
            'verification_status' => 'pending',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi partner berhasil. Akun akan diverifikasi oleh admin.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    // ── Register Buyer (UMKM) ─────────────────────────────
    // POST /api/register/buyer
    public function registerBuyer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'buyer',
            'verification_status' => 'pending',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    // ── Login (semua role) ────────────────────────────────
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Hapus token lama, buat yang baru
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    // ── Logout ────────────────────────────────────────────
    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    // ── Format response user ──────────────────────────────
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'shop_name' => $user->shop_name,
            'verification_status' => $user->verification_status,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
        ];
    }
}
