<?php

namespace App\Imports;

use App\Models\SinhVien;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class SinhVienImport implements
    ToModel,
    WithStartRow,
    WithValidation,
    SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Bỏ dòng tiêu đề
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Map dữ liệu từng dòng Excel → DB
     */
    public function model(array $row)
    {
        $maSv = strtoupper(trim($row[0]));
        $lop  = strtolower(trim($row[3]));

        return new SinhVien([
            'ma_sv'    => $maSv,
            'ho_ten'   => trim($row[1] . ' ' . $row[2]),
            'lop'      => trim($row[3]),
            'email'    => strtolower($maSv) . '@student.stu.edu.vn',
            'hinh_anh' => "uploads/hinhanh_sv/{$lop}/{$maSv}.jpg",
        ]);
    }

    /**
     * Validate từng dòng
     */
    public function rules(): array
    {
        return [
            '0' => [
                'required',
                Rule::unique('sinh_viens', 'ma_sv'),
            ],
            '3' => 'required',
        ];
    }

    /**
     * Tên cột hiển thị khi báo lỗi
     */
    public function customValidationAttributes()
    {
        return [
            '0' => 'MSSV',
            '3' => 'Lớp',
        ];
    }
}
