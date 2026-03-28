<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sinh_viens', function (Blueprint $table) {
            $table->boolean('da_train_khuon_mat')
                  ->default(false)
                  ->after('hinh_anh');

            $table->json('face_ids')
                  ->nullable()
                  ->after('da_train_khuon_mat');
        });
    }

    public function down(): void
    {
        Schema::table('sinh_viens', function (Blueprint $table) {
            $table->dropColumn(['da_train_khuon_mat', 'face_ids']);
        });
    }
};
