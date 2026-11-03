<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('percobaan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->timestamp('mulai_at');
            $t->timestamp('batas_at');
            $t->timestamp('selesai_at')->nullable();
            $t->json('urutan_soal'); // [soal_id, ...] dan urutan opsi per soal
            $t->json('jawaban')->nullable(); // {soal_id: "A" | "teks esai"}
            $t->json('nilai_esai')->nullable(); // {soal_id: skor 0..bobot}
            $t->decimal('nilai', 5, 2)->nullable(); // 0-100, null bila esai belum dikoreksi
            $t->timestamps();
            $t->unique(['kuis_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan');
    }
};
