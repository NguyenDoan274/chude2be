<?php

namespace App\Imports;

use App\Models\LichThi;
use App\Models\MonHoc;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LichThiImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
    {
        $monHoc = MonHoc::where('ma_mon', $row['ma_mon'])->first();

        if (!$monHoc) return null; // Bỏ qua nếu mã môn không tồn tại

        return new LichThi([
            'mon_hoc_id' => $monHoc->id,
            'ngay_thi'   => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_thi'])->format('Y-m-d'),
            'gio_thi'    => $row['gio_thi'],
            'phong'      => $row['phong'],
        ]);
    }
}
