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
       Schema::table('sinh_viens', function (Blueprint $table) {
            $table->float('do_chinh_xac_tb')->nullable()->after('face_ids');
            $table->unsignedInteger('so_lan_nhan_dien')->default(0)->after('do_chinh_xac_tb');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sinh_viens', function (Blueprint $table) {
            //
        });
    }
};
