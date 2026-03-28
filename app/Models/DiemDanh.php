<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    use HasFactory;

    protected $table = 'diem_danhs';

    protected $fillable = [
        'lich_thi_id',
        'sinh_vien_id',
        'ket_qua',
        'do_chinh_xac',
        'thoi_gian_dd',
        'hinh_thuc_dd',
    ];

    public function sinhVien()
{
    return $this->belongsTo(SinhVien::class);
}
    public function lichThi()
{
    return $this->belongsTo(LichThi::class);
}

}
