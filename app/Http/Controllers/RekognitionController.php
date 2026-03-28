<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use App\Models\SinhVien;
use App\Models\LichThi;
use App\Models\DiemDanh;

class RekognitionController extends Controller
{
    private $bucket = 'diemdanh-sinhvien';
    private $collection = 'sinhvien_faces';

    // =============================
    // VIEW
    // =============================
    public function uploadForm()
    {
        return view('rekognition.train', ['hideSearch' => true]);
    }

    public function index($lichThi)
    {
        $lichThi = LichThi::findOrFail($lichThi);

        $query = DiemDanh::with('sinhVien')
            ->where('lich_thi_id', $lichThi->id);

        if (request('search')) {
            $search = request('search');
            $query->whereHas('sinhVien', function ($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                  ->orWhere('ho_ten', 'like', "%{$search}%");
            });
        }

        if (request('chua_diem_danh')) {
            $query->where(function ($q) {
                $q->where('ket_qua', '!=', 'hợp lệ')
                  ->orWhereNull('ket_qua');
            });
        }

        $sinhViens = $query->get();

        return view('rekognition.index', compact('lichThi', 'sinhViens'));
    }

    // =============================
    // TRAIN (CALL LAMBDA)
    // =============================
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
                return response()->json([
                    'success' => false,
                    'message' => "Không tồn tại MSSV: $ma_sv"
                ]);
            }

            if ($sv->da_train_khuon_mat && !$force) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinh viên đã có mẫu khuôn mặt'
                ]);
            }

            if ($force && !$sv->canRetrain()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đủ điều kiện train lại',
                    'can_retrain' => false
                ]);
            }

            // Upload ảnh temp
            $tempPath = "temp/{$ma_sv}_" . uniqid() . ".jpg";
            Storage::disk('s3')->put($tempPath, file_get_contents($image), 'public');

            // Call Lambda Train
            $response = Http::post(env('LAMBDA_TRAIN_URL'), [
                'bucket' => $this->bucket,
                'imageKey' => $tempPath,
                'collectionId' => $this->collection,
                'externalImageId' => $ma_sv,
            ]);

            if (!$response->ok()) {
                Storage::disk('s3')->delete($tempPath);

                return response()->json([
                    'success' => false,
                    'message' => 'Không gọi được Lambda Train'
                ]);
            }

            $responseData = $response->json();

            if (isset($responseData['body'])) {
                $data = json_decode($responseData['body'], true);
            } else {
                $data = $responseData;
            }

            if (!$data['success']) {
                Storage::disk('s3')->delete($tempPath);

                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'Train thất bại'
                ]);
            }

            $faceIds = $data['face_ids'] ?? [];

            // SAVE DB
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =============================
    // COMPARE (CALL LAMBDA)
    // =============================
    public function compareMany(Request $request, LichThi $lichThi)
    {
        $request->validate([
            'hinh_anh_base64' => 'required',
        ]);

        try {
            // decode base64
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->hinh_anh_base64);
            $imageBytes = base64_decode($base64);

            // upload S3
            $fileName = 'temp/' . uniqid() . '.jpg';
            Storage::disk('s3')->put($fileName, $imageBytes, 'public');

            // call lambda
            $response = Http::post(env('LAMBDA_COMPARE_URL'), [
                'bucket' => $this->bucket,
                'imageKey' => $fileName,
                'collectionId' => $this->collection,
                'threshold' => 85
            ]);

            $responseData = $response->json();

            if (isset($responseData['body'])) {
                $body = json_decode($responseData['body'], true);
            } else {
                $body = $responseData;
            }

            $faces = [];

            // matched
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
                    'box' => [
                        'x' => $a['box']['left'],
                        'y' => $a['box']['top'],
                        'width' => $a['box']['width'],
                        'height' => $a['box']['height'],
                    ],
                    'name' => $a['externalImageId'],
                    'ho_ten' => $sv->ho_ten ?? null,
                    'similarity' => $a['similarity'],
                    'valid' => (bool)$sv,
                    'checkedIn' => $checked,
                    'color' => $checked ? 'yellow' : 'green'
                ];
            }

            // unknown
            foreach ($body['unknownFaces'] as $u) {
                $faces[] = [
                    'box' => [
                        'x' => $u['box']['left'],
                        'y' => $u['box']['top'],
                        'width' => $u['box']['width'],
                        'height' => $u['box']['height'],
                    ],
                    'name' => null,
                    'ho_ten' => null,
                    'similarity' => null,
                    'valid' => false,
                    'checkedIn' => false,
                    'color' => 'red'
                ];
            }

            session(["faces_{$lichThi->id}" => $faces]);

            return response()->json([
                'faces' => $faces
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =============================
    // CONFIRM ĐIỂM DANH
    // =============================
    public function confirmMany(Request $request, LichThi $lichThi)
    {
        $request->validate([
            'faces' => 'required|json',
        ]);

        $faceIndexes = json_decode($request->faces, true);
        $faces = session("faces_{$lichThi->id}", []);

        foreach ($faceIndexes as $idx) {
            if (!isset($faces[$idx])) continue;

            $face = $faces[$idx];

            if (!$face['valid'] || !$face['name']) continue;

            $sv = SinhVien::where('ma_sv', $face['name'])->first();

            DiemDanh::where('sinh_vien_id', $sv->id)
                ->where('lich_thi_id', $lichThi->id)
                ->update([
                    'ket_qua' => 'hợp lệ',
                    'do_chinh_xac' => $face['similarity'],
                    'thoi_gian_dd' => now(),
                ]);

            $faces[$idx]['checkedIn'] = true;
            $faces[$idx]['color'] = 'yellow';
        }

        session(["faces_{$lichThi->id}" => $faces]);

        return response()->json([
            'message' => 'Điểm danh thành công',
            'faces' => $faces
        ]);
    }

    public function getAttendanceData(LichThi $lichThi)
    {
        $query = DiemDanh::with('sinhVien')
            ->where('lich_thi_id', $lichThi->id);

        if (request('search')) {
            $search = request('search');
            $query->whereHas('sinhVien', function($q) use ($search) {
                $q->where('ma_sv', 'like', "%{$search}%")
                ->orWhere('ho_ten', 'like', "%{$search}%");
            });
        }

        if (request('chua_diem_danh')) {
            $query->where('ket_qua', '!=', 'hợp lệ')
                ->orWhereNull('ket_qua');
        }

        $sinhViens = $query->get();

        return view('rekognition.attendance_table', compact('sinhViens', 'lichThi'));
    }
}
