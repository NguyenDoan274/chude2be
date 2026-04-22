<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SinhVienController;
use App\Http\Controllers\GiangVienController;
use App\Http\Controllers\MonHocController;
use App\Http\Controllers\LichThiController;
use App\Http\Controllers\RekognitionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DiemDanhController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Các route ở đây sẽ tự động được thêm tiền tố "/api" (ví dụ: /api/login).
| Bạn không cần return View ở các controller nữa, chỉ return JSON.
|
*/

// ==========================================
// PUBLIC ROUTES (Không cần đăng nhập)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// PROTECTED ROUTES (Yêu cầu có Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/tai-khoan', [AuthController::class, 'profile']);
    Route::post('/tai-khoan/doi-mat-khau', [AuthController::class, 'updatePassword']);

    // Dashboard / Home (Trả về data thống kê cho trang chủ)
    Route::get('/home', [HomeController::class, 'index']);

    // ==========================================
    // ADMIN ROUTES
    // ==========================================
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        
        // Sinh Viên
        Route::apiResource('sinhvien', SinhVienController::class);
        Route::post('/sinhvien/import', [SinhVienController::class, 'import']);
        
        // Môn Học
        Route::apiResource('monhoc', MonHocController::class);
        Route::post('/monhoc/import', [MonHocController::class, 'import']);
        
        // Giảng Viên
        Route::apiResource('giangvien', GiangVienController::class);
        Route::post('/giangvien/import', [GiangVienController::class, 'import']);
        
        // Giảng Viên - Phân Công
        Route::get('/giangvien/{giangvien}/phancong', [GiangVienController::class, 'phancong']);
        Route::post('/giangvien/{giangvien}/assign/{lichthi}', [GiangVienController::class, 'assign']);
        Route::delete('/giangvien/{giangvien}/unassign/{lichthi}', [GiangVienController::class, 'unassign']);

        // Lịch Thi - Admin Actions
        Route::delete('/lichthi/{lichthi}/phancong/{phancong}', [LichThiController::class, 'xoaPhanCong']);
        Route::get('/lichthi/{id}/ket-qua', [LichThiController::class, 'showKetQua']);
        // Trả về data danh sách phân công thay vì view
        Route::get('/lichthi/{id}/phancong', [LichThiController::class, 'phanCongForm']); 
        Route::post('/lichthi/{id}/phancong', [LichThiController::class, 'phanCongSave']);

        // Rekognition (AWS Face) - Admin
        Route::post('/rekognition/train-ajax', [RekognitionController::class, 'trainAjax']);
        Route::post('/rekognition/retrain-ajax', [RekognitionController::class, 'retrainAjax']);
    });

    // ==========================================
    // GENERAL ROUTES (Cho mọi Giảng viên)
    // ==========================================
    
    // Lịch Thi
    Route::apiResource('lichthi', LichThiController::class);
    Route::get('/lichthi/{id}/export', [LichThiController::class, 'export']);
    Route::post('/lichthi/{lichthi}/add-students', [LichThiController::class, 'addStudents']);
    Route::delete('/lichthi/remove-student/{id}', [LichThiController::class, 'removeStudent']);

    // Điểm Danh
    Route::apiResource('diemdanh', DiemDanhController::class)->only(['index', 'show']);
    Route::post('/diemdanh/toggle', [DiemDanhController::class, 'toggle']);
    Route::post('/diemdanh/{lichThi}/ketthuc', [DiemDanhController::class, 'ketThucCaThi']);

    // Sinh Viên (Tìm kiếm cho Select/List)
    Route::post('/sinhvien/search-list', [SinhVienController::class, 'searchByList']);

    // Rekognition - Xử lý nhận diện
    Route::post('/rekognition/compare-many/{lichThi}', [RekognitionController::class, 'compareMany']);
    Route::post('/rekognition/confirm-many/{lichThi}', [RekognitionController::class, 'confirmMany']);
    Route::get('/rekognition/diemdanh/{lichThi}', [RekognitionController::class, 'index']); // Lấy data danh sách sinh viên thi
    Route::get('/rekognition/{lichThi}/attendance-data', [RekognitionController::class, 'getAttendanceData']);
});