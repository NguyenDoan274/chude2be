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
    Schema::create('sinh_viens', function (Blueprint $table) {
        $table->id();
        $table->string('ma_sv')->unique();
        $table->string('ho_ten');
        $table->string('lop');
        $table->string('email')->unique();
        $table->string('hinh_anh')->nullable(); // lưu URL ảnh
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinh_viens');
    }
};
