<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users dibuat sebelum kelas, jadi FK kelas_id baru bisa ditambahkan setelah tabel kelas ada
        Schema::table('users', function (Blueprint $t) {
            $t->foreign('kelas_id')->references('id')->on('kelas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropForeign(['kelas_id']));
    }
};
