<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SinhVien;

class SinhVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SinhVien::factory()->count(10)->create();
    }
}
