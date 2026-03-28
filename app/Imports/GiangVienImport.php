<?php

namespace App\Imports;

use App\Models\GiangVien;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class GiangVienImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return GiangVien::updateOrCreate(
            ['ma_gv' => $row['ma_gv']],
            [
                'ho_ten' => $row['ho_ten'],
                'email'  => $row['email'],
                'password' => Hash::make($row['password']),
                'vai_tro' => $row['vai_tro'],
            ]
        );
    }
}
