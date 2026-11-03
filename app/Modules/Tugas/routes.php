<?php

use App\Modules\Tugas\Controllers\TugasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')->group(function () {
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/tugas/baru', [TugasController::class, 'create'])->name('tugas.create');
        Route::post('/tugas', [TugasController::class, 'store'])->name('tugas.store');
        Route::get('/tugas/{tugas}/ubah', [TugasController::class, 'edit'])->name('tugas.edit');
        Route::put('/tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
        Route::delete('/tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
        Route::post('/tugas/{tugas}/nilai/{pengumpulan}', [TugasController::class, 'nilai'])->name('tugas.nilai');
    });

    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/{tugas}', [TugasController::class, 'show'])->name('tugas.show');

    Route::middleware('role:siswa')->group(function () {
        Route::post('/tugas/{tugas}/kumpul', [TugasController::class, 'kumpul'])->name('tugas.kumpul');
    });

    Route::get('/berkas/tugas/{tugas}', [TugasController::class, 'berkas'])->name('berkas.tugas');
    Route::get('/berkas/kumpulan/{pengumpulan}', [TugasController::class, 'berkasKumpulan'])->name('berkas.pengumpulan');
});
