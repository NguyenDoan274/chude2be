<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\SinhVien;

class CleanupUnusedImages extends Command
{
    /**
     * Tên lệnh artisan.
     *
     * @var string
     */
    protected $signature = 'cleanup:images';

    /**
     * Mô tả ngắn gọn.
     *
     * @var string
     */
    protected $description = 'Xóa các ảnh sinh viên không còn được sử dụng trong cơ sở dữ liệu';

    /**
     * Thực thi lệnh.
     */
    public function handle()
    {
        $this->info('🔍 Đang quét thư mục ảnh...');

        // 1️⃣ Lấy tất cả đường dẫn ảnh đang được dùng trong DB
        $usedImages = SinhVien::whereNotNull('hinh_anh')
            ->pluck('hinh_anh')
            ->map(function ($path) {
                // Chuyển 'storage/hinhanh_sv/xxx.jpg' → 'hinhanh_sv/xxx.jpg'
                return str_replace('storage/', '', $path);
            })
            ->toArray();

        // 2️⃣ Lấy tất cả file thực tế trong thư mục storage/app/public/hinhanh_sv
        $allImages = Storage::disk('public')->files('hinhanh_sv');

        // 3️⃣ Tìm file nào không nằm trong DB
        $unused = array_diff($allImages, $usedImages);

        if (empty($unused)) {
            $this->info('✅ Không có ảnh rác cần xóa.');
            return 0;
        }

        // 4️⃣ Xóa các file ảnh không còn dùng
        foreach ($unused as $file) {
            Storage::disk('public')->delete($file);
            $this->line("🗑️ Đã xóa: {$file}");
        }

        $this->info('🎉 Dọn dẹp hoàn tất!');
        return 0;
    }
}
