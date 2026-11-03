<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $t->unsignedSmallInteger('urutan');
            $t->enum('jenis', ['pg', 'esai'])->default('pg');
            $t->text('pertanyaan');
            $t->json('opsi')->nullable(); // {"A": "...", "B": "..."}
            $t->string('kunci', 1)->nullable();
            $t->unsignedTinyInteger('bobot')->default(1);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
