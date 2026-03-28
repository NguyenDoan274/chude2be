<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_this', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại môn học
            $table->unsignedBigInteger('mon_hoc_id');

            $table->date('ngay_thi');
            $table->time('gio_thi');
            $table->string('phong');
            $table->string('ky_thi')->nullable();
            $table->string('nam_hoc');

            // trang_thai: chua_dien_ra | dang_dien_ra | da_ket_thuc
            $table->string('trang_thai')->default('chua_dien_ra');

            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('mon_hoc_id')
                ->references('id')
                ->on('mon_hocs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_this');
    }
};
