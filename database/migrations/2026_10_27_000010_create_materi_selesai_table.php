<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_selesai', function (Blueprint $t) {
            $t->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $t->timestamp('selesai_at');
            $t->primary(['pertemuan_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_selesai');
    }
};
