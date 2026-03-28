<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\SinhVien>
 */
class SinhVienFactory extends Factory
{
    public function definition(): array
    {
        static $count = 1;

        return [
            'ma_sv' => 'SV' . str_pad($count++, 3, '0', STR_PAD_LEFT),
            'ho_ten' => $this->faker->name(),
            'lop' => 'CNTT' . $this->faker->numberBetween(1, 4),
            'email' => $this->faker->unique()->safeEmail(),
            // Giả lập ảnh đại diện (sẽ thay bằng ảnh thật khi tích hợp AWS S3)
            'hinh_anh' => $this->faker->imageUrl(200, 200, 'people', true, 'student'),
        ];
    }
}
