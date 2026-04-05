<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Xóa hàm showLoginForm()

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $giangvien = GiangVien::where('email', $request->email)->first();

        // Kiểm tra user và pass
        if (!$giangvien || !Hash::check($request->password, $giangvien->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401); // 401 Unauthorized
        }

        // Tạo Access Token (yêu cầu cài Laravel Sanctum)
        $token = $giangvien->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $giangvien,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        // Xóa token hiện tại của user đang gọi request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công'
        ], 200);
    }

    public function profile(Request $request)
    {
        // $request->user() sẽ lấy ra user tương ứng với token gửi lên
        return response()->json([
            'status' => 'success',
            'data' => clone $request->user()
        ], 200);
    }

    // Xóa hàm showChangePasswordForm()

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cũ không đúng'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Xóa toàn bộ token cũ bắt đăng nhập lại
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.'
        ], 200);
    }
}