<?php

namespace App\Imports;

use App\Models\MonHoc;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MonHocImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Bỏ qua dòng trống
        if (!isset($row['ma_mon']) || !isset($row['ten_mon'])) {
            return null;
        }

        // Tránh tạo trùng mã môn
        $exists = MonHoc::where('ma_mon', $row['ma_mon'])->first();
        if ($exists) {
            return null;
        }

        return new MonHoc([
            'ma_mon'  => $row['ma_mon'],
            'ten_mon' => $row['ten_mon'],
        ]);
    }
}
