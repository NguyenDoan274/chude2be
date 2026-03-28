<?php

namespace App\Http\Controllers;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $giangvien = GiangVien::where('email', $credentials['email'])->first();

        if ($giangvien && Hash::check($credentials['password'], $giangvien->password)) {
            Auth::guard('giangvien')->login($giangvien);
            $request->session()->regenerate();
            return redirect()->route('home.index');
        }

        return back()->withErrors([
            'login_error' => 'Email hoặc mật khẩu không chính xác.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('giangvien')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function profile()
    {
        $user = Auth::user();

        return view('auth.profile', compact('user'),['hideSearch' => true]);
    }

        // Hiển thị form đổi mật khẩu
    public function showChangePasswordForm()
    {
        $user = Auth::guard('giangvien')->user();
        return view('auth.changePassword', compact('user'));
    }

    // Xử lý cập nhật mật khẩu
    public function updatePassword(Request $request)
    {
        $user = Auth::guard('giangvien')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.min' => '🔒 Mật khẩu mới cần ít nhất 6 ký tự.',
            'new_password.confirmed' => '🔒 Mật khẩu không trùng khớp.',
        ]);

        // Kiểm tra mật khẩu cũ
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu cũ không đúng']);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        // ✅ Đăng xuất sau khi đổi mật khẩu
        Auth::guard('giangvien')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Chuyển về trang login
        return redirect()->route('login')->with('success', '🔒 Đổi mật khẩu thành công! Vui lòng đăng nhập lại.');
    }
}
