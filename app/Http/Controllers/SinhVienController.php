<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use Illuminate\Http\Request;
use App\Imports\SinhVienImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
class SinhVienController extends Controller
{
    // Danh sách sinh viên
   public function index(Request $request)
    {
        $query = SinhVien::query();

        // Nếu có tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%")
                ->orWhere('lop', 'like', "%{$search}%");
        }

        $sinhviens = $query->orderBy('ma_sv', 'asc')->paginate(20);
        return view('sinhvien.index', compact('sinhviens'));
    }

    // Form thêm sinh viên
    public function create()
    {
        return view('sinhvien.create',['hideSearch' => true]);
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
            'ma_sv.unique' => '⚠️ Mã số sinh viên đã tồn tại!',
            'email.unique' => '⚠️ Email đã tồn tại!',
        ]);

        /** Lớp */
        $folder = strtolower("d{$request->lop_y}_th{$request->lop_z}");
        $lop    = "D{$request->lop_y}_TH{$request->lop_z}";

        /** Chuẩn bị data */
        $data = $request->except(['lop_y', 'lop_z', 'hinh_anh']);
        $data['lop'] = $lop;

        /** Thư mục upload */
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/hinhanh_sv/{$folder}";

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        /** Upload ảnh */
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $extension = $file->getClientOriginalExtension();

            $fileName = $request->ma_sv . '.' . $extension;
            $file->move($uploadPath, $fileName);

            $data['hinh_anh'] = "uploads/hinhanh_sv/{$folder}/{$fileName}";
        }

        SinhVien::create($data);

        return redirect()
            ->route('sinhvien.index')
            ->with('success', 'Thêm sinh viên thành công!');
    }


    // Form sửa sinh viên
    public function edit($id)
    {
        $sinhvien = SinhVien::findOrFail($id);
        // Ví dụ: D23_TH09
        $lop = $sinhvien->lop;
        // D23_TH09 → 23
        $lop_y = substr($lop, 1, 2);
        // D23_TH09 → 09
        $lop_z = substr($lop, -2);

        return view('sinhvien.edit', compact('sinhvien', 'lop_y', 'lop_z'),['hideSearch' => true]);
    }


    // Cập nhật sinh viên
    public function update(Request $request, $id)
    {
        $sinhvien = SinhVien::findOrFail($id);

        $oldMaSv = $sinhvien->ma_sv;

        // Lớp cũ
        preg_match('/D(\d+)_TH(\d+)/', $sinhvien->lop, $matches);
        $oldFolder = strtolower("d{$matches[1]}_th{$matches[2]}");

        $request->validate([
            'ma_sv' => [
                'required',
                Rule::unique('sinh_viens')->ignore($sinhvien->id),
            ],
            'ho_ten' => 'required',
            'lop_y'  => 'required|digits:2',
            'lop_z'  => 'required|digits:2',
            'email'  => [
                'required',
                'email',
                Rule::unique('sinh_viens')->ignore($sinhvien->id),
            ],
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'ma_sv.unique' => '⚠️ Mã số sinh viên đã tồn tại!',
            'email.unique' => '⚠️ Email đã tồn tại!',
        ]);

        /** Lớp mới */
        $newFolder = strtolower("d{$request->lop_y}_th{$request->lop_z}");
        $lop       = "D{$request->lop_y}_TH{$request->lop_z}";

        /** Chuẩn bị data */
        $data = $request->except(['hinh_anh', 'xoa_anh', 'lop_y', 'lop_z']);
        $data['lop'] = $lop;

        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/hinhanh_sv';
        $oldPath  = $basePath . '/' . $oldFolder;
        $newPath  = $basePath . '/' . $newFolder;

        if (!file_exists($newPath)) {
            mkdir($newPath, 0755, true);
        }

        /** Xóa ảnh */
        if ($request->has('xoa_anh') && $sinhvien->hinh_anh) {
            $oldFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh;
            if (file_exists($oldFile)) unlink($oldFile);
            $data['hinh_anh'] = null;
        }

        /** Upload ảnh mới */
        if ($request->hasFile('hinh_anh')) {

            if ($sinhvien->hinh_anh) {
                $oldFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $sinhvien->hinh_anh;
                if (file_exists($oldFile)) unlink($oldFile);
            }

            $file = $request->file('hinh_anh');
            $extension = $file->getClientOriginalExtension();

            $fileName = $request->ma_sv . '.' . $extension;
            $file->move($newPath, $fileName);

            $data['hinh_anh'] = "uploads/hinhanh_sv/{$newFolder}/{$fileName}";
        }

        /** Rename + move ảnh khi đổi ma_sv hoặc lớp */
        if (
            !$request->hasFile('hinh_anh') &&
            !$request->has('xoa_anh') &&
            $sinhvien->hinh_anh &&
            ($oldMaSv !== $request->ma_sv || $oldFolder !== $newFolder)
        ) {
            $oldFile = glob($oldPath . '/' . $oldMaSv . '.*');

            if (!empty($oldFile)) {
                $extension = pathinfo($oldFile[0], PATHINFO_EXTENSION);
                $newFileName = $request->ma_sv . '.' . $extension;

                rename(
                    $oldFile[0],
                    $newPath . '/' . $newFileName
                );

                $data['hinh_anh'] = "uploads/hinhanh_sv/{$newFolder}/{$newFileName}";
            }
        }

        $sinhvien->update($data);

        return redirect()
            ->route('sinhvien.index')
            ->with('success', 'Cập nhật sinh viên thành công!');
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

            //xóa thư mục lớp nếu rỗng
            $folderPath = dirname($imagePath);
            if (is_dir($folderPath) && count(scandir($folderPath)) === 2) {
                rmdir($folderPath);
            }
        }

        $sinhvien->delete();

        return redirect()
            ->route('sinhvien.index')
            ->with('success', 'Đã xoá sinh viên và ảnh liên quan!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = new SinhVienImport();
        Excel::import($import, $request->file('file'));

        // Lấy lỗi từng dòng
        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            return back()->with([
                'import_failures' => $failures,
                'warning' => 'Import hoàn tất nhưng có một số dòng bị lỗi!',
            ]);
        }

        return back()->with('success', 'Import danh sách sinh viên thành công!');
    }
    public function searchByList(Request $request)
    {
        $mssv = $request->input('mssv'); // <-- lấy mảng MSSV từ JSON

        $sinhviens = SinhVien::whereIn('ma_sv', $mssv)->get();

        return response()->json($sinhviens);
    }
}

