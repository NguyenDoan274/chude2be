<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('diem_danhs', function (Blueprint $table) {
        $table->id();

        // Khóa ngoại liên kết đến lịch thi
        $table->foreignId('lich_thi_id')
              ->constrained('lich_this')
              ->onDelete('cascade');

        // Khóa ngoại liên kết đến sinh viên
        $table->foreignId('sinh_vien_id')
              ->constrained('sinh_viens')
              ->onDelete('cascade');

        // Mỗi sinh viên chỉ được điểm danh 1 lần cho một lịch thi
        $table->unique(['lich_thi_id', 'sinh_vien_id']);

        // Thông tin bổ sung
        $table->enum('ket_qua', ['hợp lệ', 'Vắng mặt'])->nullable();
        $table->string('do_chinh_xac')->nullable(); 
        $table->dateTime('thoi_gian_dd')->nullable(); 

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diem_danhs');
    }
};
