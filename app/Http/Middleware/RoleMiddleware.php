<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user || $user->vai_tro != $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền truy cập'
            ], 403);
        }

        return $next($request);
    }
}
