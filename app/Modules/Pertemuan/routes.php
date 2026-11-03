<?php

use App\Modules\Pertemuan\Controllers\PertemuanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')->group(function () {
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/pertemuan/baru', [PertemuanController::class, 'create'])->name('pertemuan.create');
        Route::post('/pertemuan', [PertemuanController::class, 'store'])->name('pertemuan.store');
        Route::get('/pertemuan/{pertemuan}/ubah', [PertemuanController::class, 'edit'])->name('pertemuan.edit');
        Route::put('/pertemuan/{pertemuan}', [PertemuanController::class, 'update'])->name('pertemuan.update');
        Route::delete('/pertemuan/{pertemuan}', [PertemuanController::class, 'destroy'])->name('pertemuan.destroy');
        Route::post('/pertemuan/{pertemuan}/presensi', [PertemuanController::class, 'aturPresensi'])->name('pertemuan.presensi');
    });

    Route::get('/pertemuan/{pertemuan}', [PertemuanController::class, 'show'])->name('pertemuan.show');

    Route::middleware('role:siswa')->group(function () {
        Route::post('/pertemuan/{pertemuan}/hadir', [PertemuanController::class, 'hadir'])->name('pertemuan.hadir');
        Route::post('/pertemuan/{pertemuan}/selesai', [PertemuanController::class, 'selesai'])->name('pertemuan.selesai');
    });

    Route::get('/berkas/pertemuan/{pertemuan}', [PertemuanController::class, 'berkas'])->name('berkas.pertemuan');
});
