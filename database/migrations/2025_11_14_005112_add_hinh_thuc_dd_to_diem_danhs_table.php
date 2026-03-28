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
        Schema::table('diem_danhs', function (Blueprint $table) {
            $table->string('hinh_thuc_dd')->nullable()->after('thoi_gian_dd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diem_danhs', function (Blueprint $table) {
            $table->dropColumn('hinh_thuc_dd');
        });
    }
};
