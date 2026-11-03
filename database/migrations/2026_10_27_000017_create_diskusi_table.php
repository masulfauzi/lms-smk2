<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diskusi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruang_id')->constrained('ruang')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('diskusi')->cascadeOnDelete();
            $t->text('isi');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskusi');
    }
};
