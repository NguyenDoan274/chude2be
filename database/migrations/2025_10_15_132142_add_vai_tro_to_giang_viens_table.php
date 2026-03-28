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
        Schema::table('giang_viens', function (Blueprint $table) {
             $table->string('vai_tro')->default('Giảng viên')->after('password');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giang_viens', function (Blueprint $table) {
            //
        });
    }
};
