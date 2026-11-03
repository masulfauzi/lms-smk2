<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $t->string('judul', 160);
            $t->longText('instruksi')->nullable();
            $t->string('lampiran_path')->nullable();
            $t->string('lampiran_nama')->nullable();
            $t->dateTime('tenggat');
            $t->enum('jenis_kumpul', ['file', 'teks', 'keduanya'])->default('keduanya');
            $t->boolean('boleh_terlambat')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
