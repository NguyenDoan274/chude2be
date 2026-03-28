<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mon_hocs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_mon')->unique();       
            $table->string('ten_mon');               
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mon_hocs');
    }
};
