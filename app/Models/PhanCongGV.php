<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhanCongGV extends Model
{
    use HasFactory;

    protected $table = 'phan_cong_gvs';
    protected $fillable = ['lich_thi_id', 'giang_vien_id'];
    
    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class);
    }

    public function lichThi()
    {
        return $this->belongsTo(LichThi::class);
    }
}
