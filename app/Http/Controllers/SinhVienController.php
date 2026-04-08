<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;
use App\Imports\SinhVienImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class SinhVienController extends Controller
{
    // Lấy danh sách sinh viên
    public function index(Request $request)
    {
        $query = SinhVien::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%")
                ->orWhere('lop', 'like', "%{$search}%");
        }

        // $sinhviens = $query->orderBy('ma_sv', 'asc')->paginate(20);
        $sinhviens = $query->orderBy('ma_sv', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $sinhviens
        ], 200);
         
    }

    // Lưu sinh viên mới
    public function store(Request $request)
    {
        $request->validate([
            'ma_sv'   => 'required|unique:sinh_viens',
            'ho_ten'  => 'required',
            'lop_y'   => 'required|digits:2',
            'lop_z'   => 'required|digits:2',
            'email'   => 'required|email|unique:sinh_viens',
            'hinh_anh'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'ma_sv.unique' => 'Mã số sinh viên đã tồn tại!',
            'email.unique' => 'Email đã tồn tại!',
        ]);

        $folder = strtolower("d{$request->lop_y}_th{$request->lop_z}");
        $lop    = "D{$request->lop_y}_TH{$request->lop_z}";

        $data = $request->except(['lop_y', 'lop_z', 'hinh_anh']);
        $data['lop'] = $lop;

        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/hinhanh_sv/{$folder}";
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $fileName = $request->ma_sv . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $fileName);
            $data['hinh_anh'] = "uploads/hinhanh_sv/{$folder}/{$fileName}";
        }

        $sinhvien = SinhVien::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm sinh viên thành công!',
            'data' => $sinhvien
        ], 201); // 201 Created
    }

        public function show($id)
    {
        $sinhvien = SinhVien::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $sinhvien
        ], 200);
    }

    // Cập nhật sinh viên
    public function update(Request $request, $id)
    {
        $sinhvien = SinhVien::findOrFail($id);
        $oldMaSv = $sinhvien->ma_sv;

        preg_match('/D(\d+)_TH(\d+)/', $sinhvien->lop, $matches);
        $oldFolder = strtolower("d{$matches[1]}_th{$matches[2]}");

        $request->validate([
            'ma_sv' => ['required', Rule::unique('sinh_viens')->ignore($sinhvien->id)],
            'ho_ten' => 'required',
            'lop_y'  => 'required|digits:2',
            'lop_z'  => 'required|digits:2',
            'email'  => ['required', 'email', Rule::unique('sinh_viens')->ignore($sinhvien->id)],
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $newFolder = strtolower("d{$request->lop_y}_th{$request->lop_z}");
        $lop       = "D{$request->lop_y}_TH{$request->lop_z}";

        $data = $request->except(['hinh_anh', 'xoa_anh', 'lop_y', 'lop_z']);
        $data['lop'] = $lop;

        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/hinhanh_sv';
        $oldPath  = $basePath . '/' . $oldFolder;
        $newPath  = $basePath . '/' . $newFolder;

        if (!file_exists($newPath)) {
            mkdir($newPath, 0755, true);
        }

        // Logic xử lý file ảnh giữ nguyên...
        if ($request->has('xoa_anh') && $sinhvien->hinh_anh) {
            $oldFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh;
            if (file_exists($oldFile)) unlink($oldFile);
            $data['hinh_anh'] = null;
        }

        if ($request->hasFile('hinh_anh')) {
            if ($sinhvien->hinh_anh && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh);
            }
            $file = $request->file('hinh_anh');
            $fileName = $request->ma_sv . '.' . $file->getClientOriginalExtension();
            $file->move($newPath, $fileName);
            $data['hinh_anh'] = "uploads/hinhanh_sv/{$newFolder}/{$fileName}";
        }

        $sinhvien->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật sinh viên thành công!',
            'data' => $sinhvien
        ], 200);
    }

    // Xóa sinh viên
    public function destroy($id)
    {
        $sinhvien = SinhVien::findOrFail($id);

        if ($sinhvien->hinh_anh) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $sinhvien->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xoá sinh viên và ảnh liên quan!'
        ], 200);
    }

    // Import file Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = new SinhVienImport();
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Import hoàn tất nhưng có một số dòng bị lỗi!',
                'failures' => $failures
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Import danh sách sinh viên thành công!'
        ], 200);
    }

    // Trả về JSON, giữ nguyên
    public function searchByList(Request $request)
    {
        $mssv = $request->input('mssv'); 
        $sinhviens = SinhVien::whereIn('ma_sv', $mssv)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $sinhviens
        ], 200);
    }

}