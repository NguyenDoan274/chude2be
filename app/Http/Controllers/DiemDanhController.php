<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichThi;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DiemDanhImport;
use Illuminate\Support\Facades\Auth;


class DiemDanhController extends Controller
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
        $lichThis = $query->orderBy('ngay_thi', 'desc')->orderBy('gio_thi', 'desc')->paginate(10);
        foreach ($lichThis as $lichThi) {
            $lichThi->capNhatTrangThai();
        }
        return view('diemdanh.index', compact('lichThis'),['hideSearch' => true])
            ->with('search', $request->search);
    }


    public function show(Request $request, $id)
    {
        $lichThi = LichThi::findOrFail($id);
        $lichThi->capNhatTrangThai();

        // Tạo query, chưa get()
        $sinhViensQuery = DiemDanh::with('sinhVien')->where('lich_thi_id', $id);

        // Tìm kiếm MSSV hoặc họ tên
        if ($request->filled('search')) {
            $search = $request->search;
            $sinhViensQuery->whereHas('sinhVien', function($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%")
                ->orWhere('lop', 'like', "%{$search}%");
            });
        }

        // Lọc chỉ những người chưa điểm danh
        if ($request->has('chua_diem_danh') && $request->chua_diem_danh == '1') {
            $sinhViensQuery->whereNull('ket_qua');
        }

        // Sắp xếp theo id giảm dần (mới nhất lên trước)
        $sinhViens = $sinhViensQuery->orderBy('sinh_vien_id', 'asc')->get();

        return view('diemdanh.show', compact('lichThi', 'sinhViens'),['hideSearch' => true]);
    }

    // Import danh sách sinh viên tham gia thi
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new DiemDanhImport, $request->file('file'));
            return redirect()->back()->with('success', 'Import danh sách điểm danh thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi import: ' . $e->getMessage());
        }
    }

    // Xóa một bản ghi điểm danh
    public function destroy($id)
    {
        $record = DiemDanh::findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Xóa bản ghi điểm danh thành công!');
    }

    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:diem_danhs,id',
                'checked' => 'required|boolean',
            ]);

            $diemDanh = DiemDanh::findOrFail($request->id);

            if ($request->checked) {
                $diemDanh->update([
                    'ket_qua' => 'hợp lệ',
                    'do_chinh_xac' => 100,
                    'thoi_gian_dd' => now(),
                    'hinh_thuc_dd' => 'Thủ công',
                ]);
                return response()->json(['success' => true, 'message' => 'Điểm danh thành công']);
            } else {
                $diemDanh->update([
                    'ket_qua' => null,
                    'do_chinh_xac' => null,
                    'thoi_gian_dd' => null,
                    'hinh_thuc_dd' => null,
                ]);
                return response()->json(['success' => true, 'message' => 'Đã hủy điểm danh']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
