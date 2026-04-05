<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException; 

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Giữ nguyên Alias phân quyền của bạn
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // 2. Cho phép Frontend React gọi API (CORS/Stateful)
        $middleware->statefulApi(); 
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 3. Ép trả về JSON khi có lỗi API (thay vì hiển thị mã HTML)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Bạn chưa đăng nhập hoặc Token không hợp lệ.'
                ], 401);
            }
        });
    })->create();