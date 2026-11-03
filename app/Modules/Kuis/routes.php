<?php

use App\Modules\Kuis\Controllers\KuisController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')->group(function () {
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/kuis/baru', [KuisController::class, 'create'])->name('kuis.create');
        Route::post('/kuis', [KuisController::class, 'store'])->name('kuis.store');
        Route::get('/kuis/{kuis}/ubah', [KuisController::class, 'edit'])->name('kuis.edit');
        Route::put('/kuis/{kuis}', [KuisController::class, 'update'])->name('kuis.update');
        Route::delete('/kuis/{kuis}', [KuisController::class, 'destroy'])->name('kuis.destroy');
        Route::post('/kuis/{kuis}/soal', [KuisController::class, 'simpanSoal'])->name('kuis.soal');
        Route::post('/kuis/{kuis}/impor', [KuisController::class, 'imporSoal'])->name('kuis.impor');
        Route::delete('/kuis/{kuis}/soal/{soal}', [KuisController::class, 'hapusSoal'])->name('kuis.soal.hapus');
        Route::get('/kuis/{kuis}/koreksi/{percobaan}', [KuisController::class, 'koreksi'])->name('kuis.koreksi');
        Route::post('/kuis/{kuis}/koreksi/{percobaan}', [KuisController::class, 'simpanKoreksi']);
        Route::delete('/kuis/{kuis}/percobaan/{percobaan}', [KuisController::class, 'resetPercobaan'])->name('kuis.reset');
    });

    Route::get('/kuis', [KuisController::class, 'index'])->name('kuis.index');
    Route::get('/kuis/{kuis}', [KuisController::class, 'show'])->name('kuis.show');

    Route::middleware('role:siswa')->group(function () {
        Route::post('/kuis/{kuis}/mulai', [KuisController::class, 'mulai'])->name('kuis.mulai');
        Route::get('/kuis/{kuis}/kerjakan', [KuisController::class, 'kerjakan'])->name('kuis.kerjakan');
        Route::post('/kuis/{kuis}/jawab', [KuisController::class, 'jawab'])->name('kuis.jawab');
        Route::post('/kuis/{kuis}/kumpul', [KuisController::class, 'kumpul'])->name('kuis.kumpul');
    });
});
