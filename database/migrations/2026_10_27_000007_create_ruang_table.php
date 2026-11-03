<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ruang belajar = satu mapel di satu kelas, diampu satu guru, pada satu tahun ajaran
        Schema::create('ruang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
            $t->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('deskripsi')->nullable();
            $t->unsignedTinyInteger('kkm')->default(75);
            $t->unsignedTinyInteger('bobot_tugas')->default(40); // persen
            $t->unsignedTinyInteger('bobot_kuis')->default(40);
            $t->unsignedTinyInteger('bobot_kehadiran')->default(20);
            $t->timestamps();
            $t->unique(['tahun_ajaran_id', 'kelas_id', 'mapel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang');
    }
};
