<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $t->timestamp('waktu')->nullable();
            $t->string('keterangan', 255)->nullable();
            $t->boolean('oleh_guru')->default(false);
            $t->timestamps();
            $t->unique(['pertemuan_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
