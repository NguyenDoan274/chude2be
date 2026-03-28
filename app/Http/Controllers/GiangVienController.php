<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use App\Models\LichThi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // để mã hóa mật khẩu
use App\Imports\GiangVienImport;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class GiangVienController extends Controller
{
    public function index(Request $request)
    {
        $query = GiangVien::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ho_ten', 'like', "%{$search}%");
        }

        $giangviens = $query->orderBy('id', 'desc')->get();

        return view('giangvien.index', compact('giangviens'),['hideSearch' => true]);
    }

    public function create()
    {
        return view('giangvien.create',['hideSearch' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ma_gv'   => 'required|unique:giang_viens',
            'ho_ten'  => 'required',
            'email'   => 'required|email|unique:giang_viens',
            'password' => 'required|min:6',
            'vai_tro'  => 'required',
        ], [
            'ma_gv.unique' => '⚠️ Mã giảng viên đã tồn tại trong hệ thống!',
            'password.min' => '🔒 Mật khẩu cần ít nhất 6 ký tự.',
            'ho_ten.required' => 'Vui lòng nhập họ tên.',
            'vai_tro.required' => 'Vui lòng chọn vai trò.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => '⚠️ Email đã tồn tại trong hệ thống!',
        ]);

        GiangVien::create([
            'ma_gv'    => $request->ma_gv,
            'ho_ten'   => $request->ho_ten,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // mã hóa mật khẩu
            'vai_tro'  => $request->vai_tro,
        ]);

        return redirect()->route('giangvien.index')->with('success', 'Thêm giảng viên thành công!');
    }

    public function edit($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        return view('giangvien.edit', compact('giangvien'),['hideSearch' => true]);
    }

    public function update(Request $request, $id)
    {
        $giangvien = GiangVien::findOrFail($id);

        $request->validate([
            'ma_gv' => [
                'required',
                Rule::unique('giang_viens')->ignore($giangvien->id), // Bỏ qua bản ghi hiện tại
            ],
            'ho_ten' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('giang_viens')->ignore($giangvien->id), // Bỏ qua bản ghi hiện tại
            ],
        ],[
            'ma_gv.unique' => '⚠️ Mã giảng viên đã tồn tại trong hệ thống!',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => '⚠️ Email đã tồn tại trong hệ thống!',
        ]);

        // Không cho cập nhật password hoặc vai_tro
        $giangvien->update([
            'ho_ten' => $request->ho_ten,
            'email'  => $request->email,
        ]);

        return redirect()->route('giangvien.index')->with('success', 'Cập nhật giảng viên thành công!');
    }

    public function destroy($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        $giangvien->delete();
        return redirect()->route('giangvien.index')->with('success', 'Xóa giảng viên thành công!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new GiangVienImport, $request->file('file'));

        return redirect()->route('giangvien.index')->with('success', 'Import giảng viên thành công!');
    }

    public function phancong(GiangVien $giangvien, Request $request)
    {
        // Lấy ID các lịch thi mà giảng viên đã được phân công
        $lichDaPhanCongIds = $giangvien->lichthis()->pluck('lich_thi_id');

        $query = LichThi::where('trang_thai', 'chua_dien_ra')
            ->whereNotIn('id', $lichDaPhanCongIds) // loại bỏ lịch đã phân công
            ->with(['giangviens', 'monHoc']);

        // Bộ lọc
        if($request->ten_mon){
            $query->whereHas('monHoc', fn($q) => 
                $q->where('ten_mon', 'like', '%'.$request->ten_mon.'%')
            );
        }

        if($request->phong){
            $query->where('phong', 'like', '%'.$request->phong.'%');
        }

        if($request->ngay){
            $query->where('ngay_thi', $request->ngay);
        }

        if($request->gio){
            $query->where('gio_thi', $request->gio);
        }

        $lichthis = $query->get();

        // Load danh sách lịch đã phân công (chỉ lấy lịch chưa diễn ra)
        $giangvien->load(['lichthis' => function($q){
            $q->where('trang_thai', 'chua_dien_ra')->with('monHoc');
        }]);

        return view('giangvien.phancong', compact('giangvien','lichthis'),['hideSearch' => true]);
    }

    // Phân công giảng viên
    public function assign(GiangVien $giangvien, LichThi $lichthi)
    {
        // Kiểm tra trùng lịch
        $exists = $giangvien->lichthis()
            ->where('ngay_thi', $lichthi->ngay_thi)
            ->where('gio_thi', $lichthi->gio_thi)
            ->exists();

        if($exists){
            return response()->json([
                'status' => 'conflict',
                'message' => 'Giảng viên đã có lịch thi cùng thời gian!'
            ]);
        }

        $giangvien->lichthis()->syncWithoutDetaching($lichthi->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Phân công thành công!'
        ]);
    }
    public function unassign(GiangVien $giangvien, LichThi $lichthi)
    {
        // Xóa bản ghi trong bảng trung gian
        $giangvien->lichthis()->detach($lichthi->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã hủy phân công thành công!'
        ]);
    }
}
