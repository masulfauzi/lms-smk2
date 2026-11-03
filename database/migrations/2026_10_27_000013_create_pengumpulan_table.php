<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->longText('teks')->nullable();
            $t->string('file_path')->nullable();
            $t->string('file_nama')->nullable();
            $t->timestamp('dikumpulkan_at');
            $t->boolean('terlambat')->default(false);
            $t->unsignedTinyInteger('nilai')->nullable();
            $t->text('komentar')->nullable();
            $t->timestamp('dinilai_at')->nullable();
            $t->timestamps();
            $t->unique(['tugas_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan');
    }
};
