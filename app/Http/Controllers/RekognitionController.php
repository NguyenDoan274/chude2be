<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\SinhVien;
use App\Models\LichThi;
use App\Models\DiemDanh;

class RekognitionController extends Controller
{
    private $bucket = 'diemdanh-sinhvien';
    private $collection = 'sinhvien_faces';

    public function index(Request $request, $lichThiId)
    {
        $lichThi = LichThi::findOrFail($lichThiId);

        $query = DiemDanh::with('sinhVien')
            ->where('lich_thi_id', $lichThi->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('sinhVien', function ($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                  ->orWhere('ho_ten', 'like', "%{$search}%");
            });
        }

        if ($request->filled('chua_diem_danh')) {
            $query->where(function ($q) {
                $q->where('ket_qua', '!=', 'hợp lệ')
                  ->orWhereNull('ket_qua');
            });
        }

        $sinhViens = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'lichThi' => $lichThi,
                'sinhViens' => $sinhViens
            ]
        ], 200);
    }

    public function trainAjax(Request $request)
    {
        $request->merge(['force_retrain' => 0]);
        return $this->handleTrain($request);
    }

    public function retrainAjax(Request $request)
    {
        $request->merge(['force_retrain' => 1]);
        return $this->handleTrain($request);
    }

    private function handleTrain(Request $request)
    {
        try {
            $request->validate([
                'ma_sv' => 'required',
                'hinh_anh' => 'required|mimes:jpg,jpeg,png|max:5120',
            ]);

            $ma_sv = strtoupper(trim($request->ma_sv));
            $image = $request->file('hinh_anh');
            $force = $request->boolean('force_retrain');

            $sv = SinhVien::where('ma_sv', $ma_sv)->first();
            if (!$sv) {
                return response()->json(['success' => false, 'message' => "Không tồn tại MSSV: $ma_sv"]);
            }

            if ($sv->da_train_khuon_mat && !$force) {
                return response()->json(['success' => false, 'message' => 'Sinh viên đã có mẫu khuôn mặt']);
            }

            if ($force && !$sv->canRetrain()) {
                return response()->json(['success' => false, 'message' => 'Chưa đủ điều kiện train lại', 'can_retrain' => false]);
            }

            $tempPath = "temp/{$ma_sv}_" . uniqid() . ".jpg";
            Storage::disk('s3')->put($tempPath, file_get_contents($image), 'public');

            $response = Http::post(env('LAMBDA_TRAIN_URL'), [
                'bucket' => $this->bucket,
                'imageKey' => $tempPath,
                'collectionId' => $this->collection,
                'externalImageId' => $ma_sv,
            ]);

            if (!$response->ok()) {
                Storage::disk('s3')->delete($tempPath);
                return response()->json(['success' => false, 'message' => 'Không gọi được Lambda Train']);
            }

            $responseData = $response->json();
            $data = isset($responseData['body']) ? json_decode($responseData['body'], true) : $responseData;

            if (!$data['success']) {
                Storage::disk('s3')->delete($tempPath);
                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Train thất bại']);
            }

            $faceIds = $data['face_ids'] ?? [];

            $sv->update([
                'da_train_khuon_mat' => true,
                'face_ids' => $faceIds,
                'so_lan_nhan_dien' => 0,
                'do_chinh_xac_tb' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $force ? 'Train lại thành công' : 'Train thành công',
                'face_count' => count($faceIds)
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function compareMany(Request $request, LichThi $lichThi)
    {
        $request->validate(['hinh_anh_base64' => 'required']);

        try {
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->hinh_anh_base64);
            $imageBytes = base64_decode($base64);

            $fileName = 'temp/' . uniqid() . '.jpg';
            Storage::disk('s3')->put($fileName, $imageBytes, 'public');

            $response = Http::post(env('LAMBDA_COMPARE_URL'), [
                'bucket' => $this->bucket,
                'imageKey' => $fileName,
                'collectionId' => $this->collection,
                'threshold' => 85
            ]);

            $responseData = $response->json();
            $body = isset($responseData['body']) ? json_decode($responseData['body'], true) : $responseData;

            $faces = [];

            foreach ($body['attendees'] as $a) {
                $sv = SinhVien::where('ma_sv', $a['externalImageId'])->first();
                $checked = false;
                if ($sv) {
                    $checked = DiemDanh::where('sinh_vien_id', $sv->id)
                        ->where('lich_thi_id', $lichThi->id)
                        ->where('ket_qua', 'hợp lệ')
                        ->exists();
                }

                $faces[] = [
                    'box' => ['x' => $a['box']['left'], 'y' => $a['box']['top'], 'width' => $a['box']['width'], 'height' => $a['box']['height']],
                    'name' => $a['externalImageId'],
                    'ho_ten' => $sv->ho_ten ?? null,
                    'similarity' => $a['similarity'],
                    'valid' => (bool)$sv,
                    'checkedIn' => $checked,
                    'color' => $checked ? 'yellow' : 'green'
                ];
            }

            foreach ($body['unknownFaces'] as $u) {
                $faces[] = [
                    'box' => ['x' => $u['box']['left'], 'y' => $u['box']['top'], 'width' => $u['box']['width'], 'height' => $u['box']['height']],
                    'name' => null, 'ho_ten' => null, 'similarity' => null, 'valid' => false, 'checkedIn' => false, 'color' => 'red',
                    'reason' => $u['reason'] ?? null
                ];
            }

            // session(["faces_{$lichThi->id}" => $faces]);
            return response()->json(['faces' => $faces]);

        } catch (\Throwable $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }


 public function confirmMany(Request $request, LichThi $lichThi)
{
    // Frontend sẽ gửi lên mảng 'detected_faces' chứa thông tin sv
    $request->validate([
        'detected_faces' => 'required|array',
    ]);

    $faces = $request->detected_faces;
    $count = 0;

    foreach ($faces as $face) {
        // Chỉ xử lý những khuôn mặt hợp lệ và có tên (MSSV)
        if (!isset($face['valid']) || !$face['valid'] || empty($face['name'])) {
            continue;
        }

        $sv = SinhVien::where('ma_sv', $face['name'])->first();

        if ($sv) {
            // Cập nhật bảng điểm danh
            DiemDanh::where('sinh_vien_id', $sv->id)
                ->where('lich_thi_id', $lichThi->id)
                ->update([
                    'ket_qua' => 'hợp lệ',
                    'do_chinh_xac' => $face['similarity'],
                    'thoi_gian_dd' => now(),
                ]);
            $count++;
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => "Đã điểm danh thành công {$count} sinh viên.",
    ]);
}
    public function getAttendanceData(Request $request, LichThi $lichThi)
    {
        $query = DiemDanh::with('sinhVien')->where('lich_thi_id', $lichThi->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('sinhVien', function($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%");
            });
        }

        if ($request->filled('chua_diem_danh')) {
            $query->where('ket_qua', '!=', 'hợp lệ')->orWhereNull('ket_qua');
        }

        $sinhViens = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $sinhViens
        ], 200);
    }
}