<?php

namespace App\Http\Controllers;

use App\Models\MonHoc;
use Illuminate\Http\Request;
use App\Imports\MonHocImport;
use Maatwebsite\Excel\Facades\Excel;

class MonHocController extends Controller
{
    public function index(Request $request)
    {
        $query = MonHoc::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ma_mon', 'like', "%{$search}%")
                ->orWhere('ten_mon', 'like', "%{$search}%");
        }

        $monHocs = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $monHocs
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ma_mon'  => 'required|unique:mon_hocs,ma_mon',
            'ten_mon' => 'required',
        ], [
            'ma_mon.unique' => 'Mã môn đã tồn tại trong hệ thống!',
        ]);

        $monHoc = MonHoc::create([
            'ma_mon'  => $request->ma_mon,
            'ten_mon' => $request->ten_mon,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm môn học thành công!',
            'data' => $monHoc
        ], 201);
    }

    public function show($id)
    {
        $monHoc = MonHoc::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $monHoc
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $monHoc = MonHoc::findOrFail($id);

        $request->validate([
            'ma_mon'  => 'required|unique:mon_hocs,ma_mon,' . $monHoc->id,
            'ten_mon' => 'required',
        ], [
            'ma_mon.unique' => 'Mã môn đã tồn tại trong hệ thống!',
        ]);

        $monHoc->update([
            'ma_mon'  => $request->ma_mon,
            'ten_mon' => $request->ten_mon,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật môn học thành công!',
            'data' => $monHoc
        ], 200);
    }

    public function destroy($id)
    {
        $monhoc = MonHoc::findOrFail($id);

        if ($monhoc->lichThis()->whereIn('trang_thai', ['dang_dien_ra', 'chua_dien_ra'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa môn học vì có lịch thi đang hoặc chưa diễn ra'
            ], 400);
        }

        try {
            $monhoc->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa môn học thành công'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa môn học vì còn dữ liệu lịch sử liên quan'
            ], 400);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new MonHocImport, $request->file('file'));

        return response()->json([
            'status' => 'success',
            'message' => 'Import danh sách môn học thành công!'
        ], 200);
    }
}
