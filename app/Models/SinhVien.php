<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class SinhVien extends Model
{
       use HasFactory;

        protected $table = 'sinh_viens'; 

        protected $fillable = [
        'ma_sv',
        'ho_ten',
        'lop',
        'email',
        'hinh_anh',
        'da_train_khuon_mat',
        'face_ids',
        'do_chinh_xac_tb',
        'so_lan_nhan_dien',
    ];

    protected $casts = [
        'da_train_khuon_mat' => 'boolean',
        'face_ids' => 'array',
    ];
    public function lichThis()
    {
        return $this->belongsToMany(LichThi::class, 'diem_danhs', 'sinh_vien_id', 'lich_thi_id')
                ->withPivot('ket_qua', 'do_chinh_xac', 'thoi_gian_dd', 'hinh_thuc_dd')
                    ->withTimestamps();
    }
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'sinh_vien_id');
    }
    
    public function canRetrain(): bool
    {
        return $this->so_lan_nhan_dien >= 5
            && $this->do_chinh_xac_tb < 85;
    }
}
