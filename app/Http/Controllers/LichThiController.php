<?php

namespace App\Http\Controllers;

use App\Models\LichThi;
use App\Models\MonHoc;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LichThiImport;
use App\Models\GiangVien;
use App\Models\PhanCongGV;
use App\Models\SinhVien;
use App\Models\DiemDanh;
use Illuminate\Validation\Rule;
use App\Exports\DiemDanhExport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LichThiController extends Controller
{
    public function index(Request $request)
    {
        $query = LichThi::with('monHoc')
            ->withCount(['sinhViens as so_sinh_vien', 'giangViens as so_giang_vien']);

        if ($request->filled('ten_mon')) {
            $query->whereHas('monHoc', fn($q) => $q->where('ten_mon', 'like', "%{$request->ten_mon}%"));
        }
        if ($request->filled('phong')) $query->where('phong', 'like', "%{$request->phong}%");
        if ($request->filled('ngay')) $query->where('ngay_thi', $request->ngay);
        if ($request->filled('gio')) $query->where('gio_thi', $request->gio);
        if ($request->filled('ky_thi')) $query->where('ky_thi', 'like', "%{$request->ky_thi}%");
        if ($request->filled('nam_hoc')) $query->where('nam_hoc', 'like', "%{$request->nam_hoc}%");
        if ($request->filled('trang_thai')) $query->where('trang_thai', $request->trang_thai);
        if ($request->filled('ma_sv')) {
            $ma_sv = trim($request->ma_sv);
            $query->whereHas('sinhViens', fn($q) => $q->where('ma_sv', 'like', "%$ma_sv%"));
        }
        
        $lichthis = $query->orderByRaw("FIELD(trang_thai, 'dang_dien_ra', 'chua_dien_ra', 'da_ket_thuc')")
            ->orderBy('ngay_thi', 'desc')
            ->orderBy('gio_thi', 'desc')
            ->paginate(10);

        foreach ($lichthis as $lichThi) {
            $lichThi->capNhatTrangThai();
        }

        return response()->json([
            'status' => 'success',
            'data' => $lichthis
        ], 200);
    }

    public function store(Request $request)
    {   
        $year = now()->year;
        $validNamHoc = [($year - 1) . '-' . $year, $year . '-' . ($year + 1)];

        $request->validate([
            'mon_hoc_id' => 'required|exists:mon_hocs,id',
            'ngay_thi'   => 'required|date',
            'gio_thi'    => 'required',
            'phong'      => 'required|string',
            'ky_thi'     => 'required|string',
            'nam_hoc'    => ['required', Rule::in($validNamHoc)],
        ]);

        $batDau = \Carbon\Carbon::parse($request->ngay_thi . ' ' . $request->gio_thi);
        $ketThuc = $batDau->copy()->addHour();

        if ($batDau->lessThanOrEqualTo(now())) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => ['gio_thi' => ['Thời gian thi phải lớn hơn thời điểm hiện tại']]
            ], 422);
        }

        $trungPhong = LichThi::where('phong', $request->phong)
            ->whereRaw("TIMESTAMP(ngay_thi, gio_thi) < ? AND ADDTIME(TIMESTAMP(ngay_thi, gio_thi), '01:00:00') > ?", [$ketThuc, $batDau])
            ->exists();

        if ($trungPhong) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => ['phong' => ['Phòng này đã có lịch thi trong khung giờ này']]
            ], 422);
        }

        $lichthi = LichThi::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm lịch thi thành công!',
            'data' => $lichthi
        ], 201);
    }

    public function show($id)
    {
        $lichThi = LichThi::with('monHoc')->findOrFail($id);
        $sinhViens = DiemDanh::with('sinhVien')->where('lich_thi_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'lichThi' => $lichThi,
                'sinhViens' => $sinhViens
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $lichthi = LichThi::findOrFail($id);
        $year = now()->year;
        $validNamHoc = [($year - 1) . '-' . $year, $year . '-' . ($year + 1)];

        $request->validate([
            'mon_hoc_id' => 'required|exists:mon_hocs,id',
            'ngay_thi'   => 'required|date',
            'gio_thi'    => 'required',
            'phong'      => 'required|string',
            'ky_thi'     => 'required|string',
            'nam_hoc'    => ['required', Rule::in($validNamHoc)],
        ]);

        $batDau = \Carbon\Carbon::parse($request->ngay_thi . ' ' . $request->gio_thi);
        $ketThuc = $batDau->copy()->addHour();

        if ($batDau->lessThanOrEqualTo(now())) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => ['gio_thi' => ['Thời gian thi phải lớn hơn thời điểm hiện tại']]
            ], 422);
        }

        $trungPhong = LichThi::where('phong', $request->phong)
            ->where('id', '<>', $lichthi->id)
            ->where(function($q) use ($batDau, $ketThuc) {
                $q->whereRaw("TIMESTAMP(ngay_thi, gio_thi) < ?", [$ketThuc])
                ->whereRaw("TIMESTAMP(ngay_thi, gio_thi + INTERVAL 1 HOUR) > ?", [$batDau]);
            })->exists();
        
        if ($trungPhong) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => ['phong' => ['Phòng này đã có lịch thi trong khung giờ này']]
            ], 422);
        }

        $lichthi->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật lịch thi thành công!',
            'data' => $lichthi
        ], 200);
    }

    public function destroy($id)
    {
        $lichthi = LichThi::findOrFail($id);
        $lichthi->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa lịch thi!'
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        Excel::import(new LichThiImport, $request->file('file'));
        return response()->json(['status' => 'success', 'message' => 'Import lịch thi thành công!'], 200);
    }

    public function phanCongForm($id) { 
        $lichThi = LichThi::with('phanCongGVs')->findOrFail($id); 
        $daPhanCong = $lichThi->phanCongGVs->pluck('giang_vien_id'); 
        $giangViens = GiangVien::whereNotIn('id', $daPhanCong)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'lichThi' => $lichThi,
                'giangViens' => $giangViens
            ]
        ], 200); 
    } 

    public function phanCongSave(Request $request, $id)
    {
        $request->validate([
            'lich_thi_id' => 'required|exists:lich_this,id',
            'giang_vien_id' => 'required|exists:giang_viens,id',
        ]);

        $lichThi = LichThi::findOrFail($request->lich_thi_id);
        $giangVienId = $request->giang_vien_id;

        $batDau = \Carbon\Carbon::parse($lichThi->ngay_thi . ' ' . $lichThi->gio_thi);
        $ketThuc = $batDau->copy()->addHour();

        $lichTrung = PhanCongGV::where('giang_vien_id', $giangVienId)
            ->whereHas('lichThi', function($q) use ($batDau, $ketThuc, $lichThi) {
                $q->where('id', '<>', $lichThi->id)
                ->where(function($q2) use ($batDau, $ketThuc) {
                    $q2->whereRaw("TIMESTAMP(ngay_thi, gio_thi) < ?", [$ketThuc])
                        ->whereRaw("TIMESTAMP(ngay_thi, gio_thi + INTERVAL 1 HOUR) > ?", [$batDau]);
                });
            })
            ->with(['lichThi.monHoc', 'giangVien'])
            ->first();

        if ($lichTrung) {
            $lt = $lichTrung->lichThi;
            $gv = $lichTrung->giangVien;
            return response()->json([
                'status' => 'error',
                'message' => "Giảng viên {$gv->ho_ten} đã có lịch thi môn: {$lt->monHoc->ten_mon}, Phòng: {$lt->phong} vào lúc: {$lt->ngay_thi} {$lt->gio_thi}!"
            ], 409);
        }

        $phanCong = PhanCongGV::updateOrCreate([
            'lich_thi_id' => $request->lich_thi_id,
            'giang_vien_id' => $giangVienId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Phân công giảng viên thành công!',
            'data' => $phanCong
        ], 200);
    }

    public function xoaPhanCong($lichthiId, $phancongId) { 
        $phanCong = PhanCongGV::where('lich_thi_id', $lichthiId)->where('id', $phancongId)->firstOrFail(); 
        $phanCong->delete(); 
        return response()->json(['status' => 'success', 'message' => 'Đã hủy phân công giảng viên.'], 200); 
    }

    public function addStudents(Request $request, LichThi $lichthi)
    {
        $mssvList = $request->input('mssv', []);
        if (empty($mssvList)) {
            return response()->json(['success' => false, 'msg' => 'Không có MSSV gửi lên', 'added'=>[], 'skipped'=>[]], 400);
        }

        $students = SinhVien::whereIn('ma_sv', $mssvList)->get();
        $batDau = \Carbon\Carbon::parse($lichthi->ngay_thi . ' ' . $lichthi->gio_thi);
        $ketThuc = $batDau->copy()->addHour();

        $added = [];
        $skipped = [];

        foreach ($students as $sv) {
            $daCoTrongLich = DiemDanh::where('lich_thi_id', $lichthi->id)->where('sinh_vien_id', $sv->id)->exists();
            if ($daCoTrongLich) {
                $skipped[] = "{$sv->ma_sv} – {$sv->ho_ten} (đã có trong ca thi)";
                continue;
            }

            $trungLich = DiemDanh::where('sinh_vien_id', $sv->id)
                ->whereHas('lichThi', function ($q) use ($batDau, $ketThuc, $lichthi) {
                    $q->where('id', '<>', $lichthi->id)
                    ->where(function($q2) use ($batDau, $ketThuc) {
                        $q2->whereRaw("TIMESTAMP(ngay_thi, gio_thi) < ?", [$ketThuc])
                            ->whereRaw("TIMESTAMP(ngay_thi, gio_thi + INTERVAL 1 HOUR) > ?", [$batDau]);
                    });
                })->with(['lichThi.monHoc'])->first();

            if ($trungLich) {
                $lt = $trungLich->lichThi;
                $skipped[] = "{$sv->ma_sv} – {$sv->ho_ten} (trùng môn: {$lt->monHoc->ten_mon}, phòng: {$lt->phong}, {$lt->ngay_thi} {$lt->gio_thi})";
                continue;
            }

            DiemDanh::updateOrCreate(['lich_thi_id' => $lichthi->id, 'sinh_vien_id' => $sv->id]);
            $added[] = "{$sv->ma_sv} – {$sv->ho_ten}";
        }

        return response()->json([
            'success' => count($added) > 0,
            'msg' => count($added) > 0 ? 'Thêm sinh viên thành công' : 'Không có sinh viên nào được thêm',
            'added' => $added,
            'skipped' => $skipped
        ]);
    }

    public function removeStudent($id)
    {
        $record = DiemDanh::findOrFail($id); 
        $record->delete();
        return response()->json(['status' => 'success', 'message' => 'Đã xóa sinh viên khỏi ca thi.'], 200);
    }

    public function showKetQua($id)
    {
        $lichThi = LichThi::with(['monHoc'])->findOrFail($id);
        $sinhViens = DiemDanh::where('lich_thi_id', $id)
            ->with('sinhVien')->orderBy('sinh_vien_id')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'lichThi' => $lichThi,
                'sinhViens' => $sinhViens
            ]
        ], 200);
    }
    
    // Hàm export giữ nguyên vì trả về file Excel
    public function export($id)
    {
        Log::info("Export called with ID: " . $id);
        $lichThi = LichThi::with('monHoc')->findOrFail($id);
        $tenMon = Str::slug($lichThi->monHoc->ten_mon ?? 'monhoc');
        $phong = Str::slug($lichThi->phong ?? 'phong');
        $ngayGio = $lichThi->thoi_gian_thi 
            ? $lichThi->thoi_gian_thi->format('d-m-Y') . '_' . $lichThi->thoi_gian_thi->format('G') . 'h' . $lichThi->thoi_gian_thi->format('i')
            : 'ngaythi';
        $kyThi = Str::slug($lichThi->ky_thi ?? 'kythi');
        $namHoc = Str::slug($lichThi->nam_hoc ?? 'namhoc');
        
        $filename = "{$tenMon}_{$phong}_{$ngayGio}_{$kyThi}_{$namHoc}.xlsx";
        
        return Excel::download(new DiemDanhExport($id), $filename);
    }
}