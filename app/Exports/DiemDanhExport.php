<?php

namespace App\Exports;

use App\Models\DiemDanh;
use App\Models\LichThi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DiemDanhExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    protected $lichThiId;
    protected $lichThi;

    public function __construct($lichThiId)
    {
        $this->lichThiId = $lichThiId;
        $this->lichThi = LichThi::with('monHoc')->find($lichThiId);
    }

    public function collection()
    {
        if (!$this->lichThi) {
            return collect([
                ['LỖI: Không tìm thấy thông tin lịch thi với ID: ' . $this->lichThiId]
            ]);
        }

        $diemDanhs = DiemDanh::with(['sinhVien', 'lichThi.monHoc'])
            ->where('lich_thi_id', $this->lichThiId)
            ->get();

        $data = [];

        $data[] = [ // Dòng 1
            'Môn học:', 
            $this->lichThi->monHoc->ten_mon ?? 'monhoc',
            '', '', '', '', '', ''
        ];

        $data[] = [ // Dòng 2
            'Phòng thi:', 
            $this->lichThi->phong ?? 'phong',
            '', '', '', '', '', ''
        ];

        $data[] = [ // Dòng 3
            'Ngày thi:', 
            $this->lichThi->thoi_gian_thi ? $this->lichThi->thoi_gian_thi->format('d/m/Y H:i') : 'ngaythi',
            '', '', '', '', '', ''
        ];

        $data[] = [ // Dòng 4
            'Kỳ thi:', 
            $this->lichThi->ky_thi ?? 'kythi',
            '', '', '', '', '', ''
        ];

        $data[] = [ // Dòng 5
            'Năm học:', 
            $this->lichThi->nam_hoc ?? 'namhoc',
            '', '', '', '', '', ''
        ];

        // Dòng trống
        $data[] = ['', '', '', '', '', '', '', ''];

        // Tiêu đề bảng (dòng tiếp theo)
        $data[] = [
            'Mã sinh viên',
            'Họ tên', 
            'Lớp',
            'Kết quả điểm danh',
            'Độ chính xác',
            'Thời gian điểm danh',
            'Hình thức',
            'Ghi chú'
        ];

        // Dữ liệu sinh viên
        $coMat = 0;
        $vangMat = 0;

        foreach ($diemDanhs as $diemDanh) {
            $ketQua = $diemDanh->ket_qua === 'hợp lệ' ? 'Có mặt' : 'Vắng mặt';
            
            if ($ketQua === 'Có mặt') {
                $coMat++;
            } else {
                $vangMat++;
            }

            $data[] = [
                $diemDanh->sinhVien->ma_sv ?? 'N/A',
                $diemDanh->sinhVien->ho_ten ?? 'N/A',
                $diemDanh->sinhVien->lop ?? 'N/A',
                $ketQua,
                $diemDanh->do_chinh_xac ? $diemDanh->do_chinh_xac . '%' : '',
                $diemDanh->thoi_gian_dd ? \Carbon\Carbon::parse($diemDanh->thoi_gian_dd)->format('d/m/Y H:i') : '',
                $diemDanh->hinh_thuc_dd ?? '',
                ''
            ];
        }

        // Dòng trống
        $data[] = ['', '', '', '', '', '', '', ''];

        // Phần thống kê
        $tongSV = $coMat + $vangMat;
        $tyLeCoMat = $tongSV > 0 ? round(($coMat / $tongSV) * 100, 2) : 0;

        $data[] = ['THỐNG KÊ', '', '', '', '', '', '', ''];
        $data[] = ['Tổng số sinh viên:', $tongSV, '', '', '', '', '', ''];
        $data[] = ['Số sinh viên có mặt:', $coMat, '', '', '', '', '', ''];
        $data[] = ['Số sinh viên vắng mặt:', $vangMat, '', '', '', '', '', ''];
        $data[] = ['Tỷ lệ có mặt:', $tyLeCoMat . '%', '', '', '', '', '', ''];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'BÁO CÁO ĐIỂM DANH',
            '', '', '', '', '', '', ''
        ];
    }

    public function title(): string
    {
        return 'Kết quả điểm danh';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge cells cho tiêu đề chính
                $event->sheet->mergeCells('A1:H1');
            
                // Style cho tiêu đề
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Style cho thông tin lịch thi (các dòng 2-6)
                $event->sheet->getStyle('A2:A6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                // Style cho tiêu đề bảng (dòng 8)
                $startDataRow = 8; // Dòng bắt đầu bảng dữ liệu
                $event->sheet->getStyle("A{$startDataRow}:H{$startDataRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFE6E6FA'],
                    ]
                ]);

                // Style cho phần thống kê
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A{$lastRow}:A" . ($lastRow - 4))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                $event->sheet->getStyle("A{$lastRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ]
                ]);
            },
        ];
    }
}