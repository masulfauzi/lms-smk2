<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->unsignedTinyInteger('hari'); // 1 = Senin ... 6 = Sabtu
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->string('link_meet')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
