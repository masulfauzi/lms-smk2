<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 60)->primary();
            $t->text('nilai')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
