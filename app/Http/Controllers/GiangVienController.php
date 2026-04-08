<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use App\Models\LichThi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            $query->where('ho_ten', 'like', "%{$search}%")
                  ->orWhere('ma_gv', 'like', "%{$search}%"); // Tìm theo cả mã cho tiện
        }

        $giangviens = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $giangviens
        ], 200);
    }

    public function store(Request $request)
    {
        // THÊM THÔNG BÁO LỖI TIẾNG VIỆT
        $request->validate([
            'ma_gv'    => 'required|unique:giang_viens',
            'ho_ten'   => 'required',
            'email'    => 'required|email|unique:giang_viens',
            'password' => 'required|min:6',
            'vai_tro'  => 'required',
        ], [
            'ma_gv.required' => 'Không được để trống Mã giảng viên.',
            'ma_gv.unique'   => 'Mã giảng viên này đã tồn tại trong hệ thống!',
            'ho_ten.required'=> 'Không được để trống Họ tên.',
            'email.required' => 'Không được để trống Email.',
            'email.unique'   => 'Email này đã có người sử dụng!',
            'password.required'=> 'Không được để trống Mật khẩu.',
            'password.min'   => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'vai_tro.required'=> 'Vui lòng chọn Vai trò cho giảng viên.',
        ]);

        $giangvien = GiangVien::create([
            'ma_gv'    => $request->ma_gv,
            'ho_ten'   => $request->ho_ten,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'vai_tro'  => $request->vai_tro,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm giảng viên thành công!',
            'data' => $giangvien
        ], 201);
    }

    public function show($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $giangvien
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $giangvien = GiangVien::findOrFail($id);

        // THÊM THÔNG BÁO LỖI TIẾNG VIỆT VÀ KIỂM TRA MÃ TRÙNG
        $request->validate([
            'ma_gv' => ['required', Rule::unique('giang_viens')->ignore($giangvien->id)],
            'ho_ten' => 'required',
            'email' => ['required', 'email', Rule::unique('giang_viens')->ignore($giangvien->id)],
            'vai_tro' => 'required',
        ], [
            'ma_gv.required' => 'Không được để trống Mã giảng viên.',
            'ma_gv.unique'   => 'Mã giảng viên này đã trùng với người khác. Vui lòng nhập mã khác!',
            'ho_ten.required'=> 'Không được để trống Họ tên.',
            'email.required' => 'Không được để trống Email.',
            'email.unique'   => 'Email này đã có người sử dụng!',
            'vai_tro.required'=> 'Vui lòng chọn Vai trò.',
        ]);

        // ĐÃ SỬA LỖI Ở ĐÂY: Thêm ma_gv và vai_tro vào lệnh update
        $giangvien->update([
            'ma_gv'   => $request->ma_gv,
            'ho_ten'  => $request->ho_ten,
            'email'   => $request->email,
            'vai_tro' => $request->vai_tro,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật giảng viên thành công!',
            'data' => $giangvien
        ], 200);
    }

    public function destroy($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        $giangvien->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa giảng viên thành công!'
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new GiangVienImport, $request->file('file'));

        return response()->json([
            'status' => 'success',
            'message' => 'Import giảng viên thành công!'
        ], 200);
    }

    public function phancong(GiangVien $giangvien, Request $request)
    {
        $lichDaPhanCongIds = $giangvien->lichthis()->pluck('lich_thi_id');

        $query = LichThi::where('trang_thai', 'chua_dien_ra')
            ->whereNotIn('id', $lichDaPhanCongIds)
            ->with(['giangviens', 'monHoc']);

        if($request->ten_mon){
            $query->whereHas('monHoc', fn($q) => $q->where('ten_mon', 'like', '%'.$request->ten_mon.'%'));
        }
        if($request->phong) $query->where('phong', 'like', '%'.$request->phong.'%');
        if($request->ngay) $query->where('ngay_thi', $request->ngay);
        if($request->gio) $query->where('gio_thi', $request->gio);

        $lichthis = $query->get();

        $giangvien->load(['lichthis' => function($q){
            $q->where('trang_thai', 'chua_dien_ra')->with('monHoc');
        }]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'giangvien' => $giangvien,
                'lich_thi_kha_dung' => $lichthis
            ]
        ], 200);
    }

    public function assign(GiangVien $giangvien, LichThi $lichthi)
    {
        $exists = $giangvien->lichthis()
            ->where('ngay_thi', $lichthi->ngay_thi)
            ->where('gio_thi', $lichthi->gio_thi)
            ->exists();
            if($exists){
            return response()->json([
                'status' => 'conflict',
                'message' => 'Giảng viên đã có lịch thi cùng thời gian!'
            ], 409);
        }

        $giangvien->lichthis()->syncWithoutDetaching($lichthi->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Phân công thành công!'
        ], 200);
    }

    public function unassign(GiangVien $giangvien, LichThi $lichthi)
    {
        $giangvien->lichthis()->detach($lichthi->id);
        return response()->json([
            'status' => 'success',
            'message' => 'Đã hủy phân công thành công!'
        ], 200);
    }
}