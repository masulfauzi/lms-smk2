<?php

use App\Modules\Kelas\Controllers\KelasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas');
    Route::post('/kelas', [KelasController::class, 'simpan']);
    Route::delete('/kelas/{kelas}', [KelasController::class, 'hapus'])->name('kelas.hapus');
});
