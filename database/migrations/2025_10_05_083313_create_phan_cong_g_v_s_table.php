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
        Schema::create('phan_cong_gvs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lich_thi_id')->constrained('lich_this')->onDelete('cascade');
        $table->foreignId('giang_vien_id')->constrained('giang_viens')->onDelete('cascade');
        $table->timestamps();
        $table->unique(['lich_thi_id', 'giang_vien_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan_cong_gvs');
    }
};
