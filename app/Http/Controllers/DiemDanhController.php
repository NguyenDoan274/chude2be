<?php

namespace App\Http\Controllers;

use App\Models\DiemDanh;
use App\Models\LichThi;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DiemDanhImport;

class DiemDanhController extends Controller
{
    public function index(Request $request)
    {   
        $user = $request->user();
        $query = LichThi::with('monHoc');

        if ($user && in_array($user->vai_tro, ['giang_vien', 'admin'])) {
            $query->whereHas('phanCongGVs', function ($q) use ($user) {
                $q->where('giang_vien_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('phong', 'like', "%$search%")
                ->orWhereHas('monHoc', fn($sub) => $sub->where('ten_mon', 'like', "%$search%"));
            });
        }

        $lichThis = $query->orderBy('ngay_thi', 'desc')->orderBy('gio_thi', 'desc')->paginate(10);
        foreach ($lichThis as $lichThi) {
            $lichThi->capNhatTrangThai();
        }

        return response()->json([
            'status' => 'success',
            'data' => $lichThis
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $lichThi = LichThi::findOrFail($id);
        $lichThi->capNhatTrangThai();

        $sinhViensQuery = DiemDanh::with('sinhVien')->where('lich_thi_id', $id);

        if ($request->filled('search')) {
            $search = $request->search;
            $sinhViensQuery->whereHas('sinhVien', function($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%")
                ->orWhere('lop', 'like', "%{$search}%");
            });
        }

        if ($request->has('chua_diem_danh') && $request->chua_diem_danh == '1') {
            $sinhViensQuery->whereNull('ket_qua');
        }

        $sinhViens = $sinhViensQuery->orderBy('sinh_vien_id', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'lichThi' => $lichThi,
                'sinhViens' => $sinhViens
            ]
        ], 200);
    }

    // public function import(Request $request)
    // {
    //     $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

    //     try {
    //         Excel::import(new DiemDanhImport, $request->file('file'));
    //         return response()->json(['status' => 'success', 'message' => 'Import danh sách điểm danh thành công!'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => 'Lỗi khi import: ' . $e->getMessage()], 500);
    //     }
    // }

    public function destroy($id)
    {
        $record = DiemDanh::findOrFail($id);
        $record->delete();
        return response()->json(['status' => 'success', 'message' => 'Xóa bản ghi điểm danh thành công!'], 200);
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
                return response()->json(['success' => true, 'message' => 'Điểm danh thành công', 'data' => $diemDanh]);
            } else {
                $diemDanh->update([
                    'ket_qua' => null,
                    'do_chinh_xac' => null,
                    'thoi_gian_dd' => null,
                    'hinh_thuc_dd' => null,
                ]);
                return response()->json(['success' => true, 'message' => 'Đã hủy điểm danh', 'data' => $diemDanh]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}