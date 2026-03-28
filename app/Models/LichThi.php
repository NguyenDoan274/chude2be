<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichThi extends Model
{
    use HasFactory;

    protected $table = 'lich_this';

    protected $fillable = [
        'mon_hoc_id',
        'ngay_thi',
        'gio_thi',
        'phong',
        'ky_thi',
        'nam_hoc',
        'trang_thai',
    ];

    public function phanCongGVs()
    {
        return $this->hasMany(PhanCongGV::class);
    }
    /**
     * Quan hệ: Lịch thi thuộc về 1 môn học
     */
    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($lichThi) {
            $lichThi->diemDanhs()->delete();
            $lichThi->phanCongGVs()->delete();
        });
        static::updated(function ($lichThi) {
            // Nếu trạng thái chuyển sang 'da_ket_thuc'
            if ($lichThi->isDirty('trang_thai') && $lichThi->trang_thai === 'da_ket_thuc') {
                // Cập nhật tất cả sinh viên chưa điểm danh thành "Vắng mặt"
                \App\Models\DiemDanh::where('lich_thi_id', $lichThi->id)
                    ->where(function($query) {
                        $query->where('ket_qua', '!=', 'hợp lệ')
                              ->orWhereNull('ket_qua');
                    })
                    ->update([
                        'ket_qua' => 'Vắng mặt',
                    ]);
            }
        });
    }
    public function sinhViens()
    {
        return $this->belongsToMany(SinhVien::class, 'diem_danhs', 'lich_thi_id', 'sinh_vien_id')
                    ->withPivot('ket_qua', 'do_chinh_xac', 'thoi_gian_dd', 'hinh_thuc_dd')
                    ->withTimestamps();
    }
    public function giangViens()
    {
        return $this->belongsToMany(
            GiangVien::class,
            'phan_cong_gvs',   
            'lich_thi_id',     
            'giang_vien_id'    
        )->withTimestamps();
    }
    public function getThoiGianThiAttribute()
    {
        return \Carbon\Carbon::parse($this->ngay_thi . ' ' . $this->gio_thi);
    }

    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'lich_thi_id');
    }

    public function capNhatTrangThai()
    {
        $now = \Carbon\Carbon::now();

        // Thời điểm bắt đầu thi
        $thoiGianBatDau = \Carbon\Carbon::parse($this->ngay_thi . ' ' . $this->gio_thi);

        // Thời điểm kết thúc (sau 1 tiếng)
        $thoiGianKetThuc = $thoiGianBatDau->copy()->addHour();

        // Trước giờ thi → chưa diễn ra
        if ($now->lt($thoiGianBatDau)) {
            if ($this->trang_thai !== 'chua_dien_ra') {
                $this->update(['trang_thai' => 'chua_dien_ra']);
            }
        }
        // Đang trong thời gian thi → đang diễn ra
        elseif ($now->between($thoiGianBatDau, $thoiGianKetThuc)) {
            if ($this->trang_thai !== 'dang_dien_ra') {
                $this->update(['trang_thai' => 'dang_dien_ra']);
            }
        }
        // Sau 1 tiếng → đã kết thúc
        else {
            if ($this->trang_thai !== 'da_ket_thuc') {
                $this->update(['trang_thai' => 'da_ket_thuc']);
            }
        }
    }

}
