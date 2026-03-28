<?php

namespace App\Http\Controllers;

use App\Models\MonHoc;
use Illuminate\Http\Request;
use App\Imports\MonHocImport;
use Maatwebsite\Excel\Facades\Excel;
class MonHocController extends Controller
{
    /**
     * Hiển thị danh sách môn học
     */
    public function index(Request $request)
    {
        $query = MonHoc::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ma_mon', 'like', "%{$search}%")
                ->orWhere('ten_mon', 'like', "%{$search}%");
        }

        $monHocs = $query->orderBy('id', 'desc')->get(); // Sắp xếp mới nhất lên trên

        return view('monhoc.index', compact('monHocs'),['hideSearch' => true]);
    }

    /**
     * Form tạo môn học
     */
    public function create()
    {
        return view('monhoc.create',['hideSearch' => true]);
    }

    /**
     * Lưu môn học mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'ma_mon'  => 'required|unique:mon_hocs,ma_mon',
            'ten_mon' => 'required',
        ],[
            'ma_mon.unique' => '⚠️ Mã môn đã tồn tại trong hệ thống!',
        ]);

        MonHoc::create([
            'ma_mon'  => $request->ma_mon,
            'ten_mon' => $request->ten_mon,
        ]);

        return redirect()
            ->route('monhoc.index')
            ->with('success', 'Thêm môn học thành công!');
    }

    /**
     * Form chỉnh sửa môn học
     */
    public function edit($id)
    {
        $monHoc = MonHoc::findOrFail($id);
        return view('monhoc.edit', compact('monHoc'),['hideSearch' => true]);
    }

    /**
     * Cập nhật môn học
     */
    public function update(Request $request, $id)
    {
        $monHoc = MonHoc::findOrFail($id);

        $request->validate([
            'ma_mon'  => 'required|unique:mon_hocs,ma_mon,' . $monHoc->id,
            'ten_mon' => 'required',
        ],[
            'ma_mon.unique' => '⚠️ Mã môn đã tồn tại trong hệ thống!',
        ]);

        $monHoc->update([
            'ma_mon'  => $request->ma_mon,
            'ten_mon' => $request->ten_mon,
        ]);

        return redirect()
            ->route('monhoc.index')
            ->with('success', 'Cập nhật môn học thành công!');
    }

    /**
     * Xóa môn học
     */
    public function destroy($id)
    {
        $monhoc = Monhoc::findOrFail($id);

        // ❗ Kiểm tra nghiệp vụ
        if ($monhoc->lichThis()
            ->whereIn('trang_thai', ['dang_dien_ra', 'chua_dien_ra'])
            ->exists()
        ) {
            return redirect()->back()->with(
                'error',
                'Không thể xóa môn học vì có lịch thi đang diễn ra hoặc chưa diễn ra'
            );
        }

        try {
            $monhoc->delete();
            return redirect()
                ->route('monhoc.index')
                ->with('success', 'Xóa môn học thành công');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with(
                'error',
                'Không thể xóa môn học vì còn dữ liệu lịch sử liên quan'
            );
        }
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new MonHocImport, $request->file('file'));

        return redirect()->route('monhoc.index')
                        ->with('success', 'Import danh sách môn học thành công!');
    }
}
