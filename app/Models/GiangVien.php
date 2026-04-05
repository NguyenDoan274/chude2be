<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class GiangVien extends Authenticatable
{
   use HasApiTokens, Notifiable;

    protected $table = 'giang_viens';

    protected $fillable = [
        'ma_gv',
        'ho_ten',
        'email',
        'password', 
        'vai_tro',
    ];

    // Ẩn cột password khi trả dữ liệu JSON
    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($giangVien) {
            $giangVien->phanCongGVs()->delete();
        });
    }
    public function phanCongGVs()
    {
        return $this->hasMany(PhanCongGV::class, 'giang_vien_id');
    }
    public function lichThis()
    {
        return $this->belongsToMany(
            LichThi::class,
            'phan_cong_gvs',
            'giang_vien_id',
            'lich_thi_id'
        )->withTimestamps();
    }
}

