<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 15)->unique();
            $t->string('nama', 120);
            $t->enum('kelompok', ['Umum', 'Kejuruan', 'Muatan Lokal'])->default('Umum');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};
