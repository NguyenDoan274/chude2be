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
       Schema::create('giang_viens', function (Blueprint $table) {
        $table->id();
        $table->string('ma_gv')->unique();
        $table->string('ho_ten');
        $table->string('email')->unique();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giang_viens');
    }
};
