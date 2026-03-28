<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LichThi;
use Illuminate\Support\Facades\Auth;
class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LichThi::with('monHoc');
        // Nếu đã đăng nhập và là admin hoặc giang viên -> chỉ xem lịch thi được phân công
        if ($user && in_array($user->vai_tro, ['giang_vien', 'admin'])) {
            $query->whereHas('phanCongGVs', function ($q) use ($user) {
                $q->where('giang_vien_id', $user->id);
            });
        }
        // Lọc theo từ khóa tìm kiếm
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                // Tìm theo phòng
                $q->where('phong', 'like', "%$search%")
                // Tìm theo tên môn
                ->orWhereHas('monHoc', function ($sub) use ($search) {
                    $sub->where('ten_mon', 'like', "%$search%");
                });
            });
        }

        //Sắp xếp thời gian tăng dần
        $lichThis = $query->orderBy('ngay_thi', 'asc')
                    ->orderBy('gio_thi', 'asc')
                    ->paginate(10);

        foreach ($lichThis as $lichThi) {
            $lichThi->capNhatTrangThai();
        }
        return view('home.index', compact('lichThis'));
    }
}
