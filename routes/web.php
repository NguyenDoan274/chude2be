<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SinhVienController;
use App\Http\Controllers\GiangVienController;
use App\Http\Controllers\MonHocController;
use App\Http\Controllers\LichThiController;
use App\Http\Controllers\RekognitionController;
use App\Http\Controllers\AuthController;

Route::get('/dangnhap', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\HomeController;

use App\Http\Controllers\DiemDanhController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::middleware('auth:giangvien')->group(function () {
    //test đăng nhập
    Route::get('/test-auth', function () {
    dd(Auth::guard('giangvien')->check());
    });
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {

    Route::get('/sinhvien', [SinhVienController::class, 'index'])->name('sinhvien.index');
    Route::get('/sinhvien/create', [SinhVienController::class, 'create'])->name('sinhvien.create');
    Route::post('/sinhvien', [SinhVienController::class, 'store'])->name('sinhvien.store');
    Route::get('/sinhvien/{id}/edit', [SinhVienController::class, 'edit'])->name('sinhvien.edit');
    Route::put('/sinhvien/{id}', [SinhVienController::class, 'update'])->name('sinhvien.update');
    Route::delete('/sinhvien/{id}', [SinhVienController::class, 'destroy'])->name('sinhvien.destroy');
    Route::post('/sinhvien/import', [SinhVienController::class, 'import'])->name('sinhvien.import');
    Route::post('/giangvien/import', [GiangVienController::class, 'import'])->name('giangvien.import');
    Route::resource('monhoc', MonHocController::class);
    Route::post('monhoc/import', [MonHocController::class, 'import'])->name('monhoc.import');
    
    Route::resource('giangvien', GiangVienController::class);
    

    //Trang upload khuôn mặt sinh viên
    Route::get('/rekognition/upload', [RekognitionController::class, 'uploadForm'])->name('rekognition.uploadForm');
    Route::post('/rekognition/upload', [RekognitionController::class, 'train'])->name('rekognition.train');
    Route::delete('/rekognition/delete/{studentId}', [RekognitionController::class, 'deleteStudent'])->name('rekognition.delete');
    Route::get('/rekognition/delete', function () {
    return view('rekognition.delete');})->name('rekognition.delete.form');

    Route::post('/rekognition/train-ajax', [RekognitionController::class, 'trainAjax'])->name('rekognition.train.ajax');
    Route::post('/rekognition/retrain', [RekognitionController::class, 'retrainAjax'])->name('rekognition.retrain.ajax');
   
    Route::get('giangvien/{giangvien}/phancong', [GiangVienController::class, 'phancong'])
    ->name('giangvien.phancong');
    Route::post('giangvien/{giangvien}/assign/{lichthi}', [GiangVienController::class, 'assign'])
    ->name('giangvien.assign');
    Route::delete('/giangvien/{giangvien}/unassign/{lichthi}', [GiangVienController::class, 'unassign']
    )->name('giangvien.unassign');

    Route::delete('/lichthi/{lichthi}/phancong/{phancong}', [LichThiController::class, 'xoaPhanCong'])->name('lichthi.phancong.xoa');
    Route::get('/lichthi/{id}/ket-qua', [LichThiController::class, 'showKetQua'])->name('lichthi.ketqua');
    Route::get('lichthi/{id}/phancong', [LichThiController::class, 'phanCongForm'])->name('lichthi.phancong');
    Route::post('lichthi/{id}/phancong', [LichThiController::class, 'phanCongSave'])->name('lichthi.phancong.save');
    });

    Route::post('/rekognition/compare-many/{lichThi}', [RekognitionController::class, 'compareMany'])
    ->name('rekognition.compareMany');
     Route::post('/rekognition/confirm-many/{lichThi}', [RekognitionController::class, 'confirmMany'])
    ->name('rekognition.confirmMany');

    Route::resource('lichthi', LichThiController::class);
    //Route::post('/lichthi/import', [LichThiController::class, 'import'])->name('lichthi.import');

    Route::resource('diemdanh', DiemDanhController::class)->only(['index', 'show']);
    //Route::post('/diemdanh/import', [DiemDanhController::class, 'import'])->name('diemdanh.import');
    Route::get('/lichthi/{id}/export', [LichThiController::class, 'export'])->name('lichthi.export');
    //Route::get('/rekognition/create', [RekognitionController::class, 'createCollection']);

    //Trang điểm danh (theo lịch thi cụ thể)
    Route::get('/rekognition/diemdanh/{lichThi}', [RekognitionController::class, 'index'])->name('rekognition.index');

    Route::post('/diemdanh/toggle', [App\Http\Controllers\DiemDanhController::class, 'toggle'])->name('diemdanh.toggle');

    Route::post('/diemdanh/{lichThi}/ketthuc', [DiemDanhController::class, 'ketThucCaThi'])->name('diemdanh.ketthuc');

    Route::post('/sinhvien/search-list', [SinhVienController::class, 'searchByList'])->name('sinhvien.searchByList');

    Route::post('/lichthi/{lichthi}/add-students', [LichThiController::class, 'addStudents'])->name('lichthi.addStudents');

    Route::delete('/lichthi/remove-student/{id}', [LichThiController::class, 'removeStudent'])->name('lichthi.removeStudent');

    

    Route::get('/tai-khoan', [AuthController::class, 'profile'])->name('auth.profile');
    Route::get('/tai-khoan/doi-mat-khau', [AuthController::class, 'showChangePasswordForm'])
        ->name('auth.changePassword');
    Route::post('/tai-khoan/doi-mat-khau', [AuthController::class, 'updatePassword'])
        ->name('auth.updatePassword');
        
    Route::get('/rekognition/{lichThi}/attendance-data', [RekognitionController::class, 'getAttendanceData'])
    ->name('rekognition.getAttendanceData');
});


