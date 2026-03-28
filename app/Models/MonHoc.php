<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonHoc extends Model
{
    use HasFactory;

    protected $table = 'mon_hocs';

    protected $fillable = [
        'ma_mon',
        'ten_mon',
    ];

    /**
     * Quan hệ: 1 môn học có nhiều lịch thi
     */
    public function lichThis()
    {
        return $this->hasMany(LichThi::class, 'mon_hoc_id');
    }
}
