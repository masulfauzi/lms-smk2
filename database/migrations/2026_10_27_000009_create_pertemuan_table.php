<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pertemuan: materi + presensi daring dalam satu sesi
        Schema::create('pertemuan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->unsignedSmallInteger('urutan');
            $t->string('judul', 160);
            $t->longText('isi')->nullable();
            $t->string('video_url')->nullable();
            $t->string('lampiran_path')->nullable();
            $t->string('lampiran_nama')->nullable();
            $t->date('tanggal');
            $t->dateTime('presensi_buka')->nullable();
            $t->dateTime('presensi_tutup')->nullable();
            $t->boolean('terbit')->default(true);
            $t->timestamps();
            $t->index(['ruang_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
