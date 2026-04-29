<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Contoh penggunaan di routes/api.php:
     *   Route::middleware(['auth:sanctum', 'role:partner,admin'])->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Akses ditolak. Role tidak diizinkan.',
            ], 403);
        }

        return $next($request);
    }
}
