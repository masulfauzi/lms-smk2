<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('judul', 160);
            $t->text('isi');
            $t->enum('sasaran', ['semua', 'guru', 'siswa'])->default('semua');
            $t->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete();
            $t->boolean('disematkan')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
