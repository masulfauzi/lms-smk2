<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->unsignedTinyInteger('tingkat'); // 1-12
            $t->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $t->string('nama', 40)->unique(); // contoh: VII A, X TKJ 1
            $t->foreignId('wali_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
