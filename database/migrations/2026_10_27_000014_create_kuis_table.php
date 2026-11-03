<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->string('judul', 160);
            $t->text('petunjuk')->nullable();
            $t->dateTime('mulai');
            $t->dateTime('selesai');
            $t->unsignedSmallInteger('durasi_menit')->default(60);
            $t->boolean('acak_soal')->default(true);
            $t->boolean('acak_opsi')->default(true);
            $t->boolean('tampilkan_nilai')->default(true);
            $t->boolean('terbit')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};
