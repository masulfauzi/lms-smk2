<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 20); // 2026/2027
            $t->enum('semester', ['Ganjil', 'Genap']);
            $t->boolean('aktif')->default(false);
            $t->timestamps();
            $t->unique(['nama', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};
