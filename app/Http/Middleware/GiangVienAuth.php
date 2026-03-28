<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiangVienAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu giảng viên chưa đăng nhập
        if (!Auth::guard('giangvien')->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập trước khi truy cập!');
        }

        return $next($request);
    }
}
