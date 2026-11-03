<?php

use App\Modules\Pengguna\Controllers\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/pengguna/{role}', [PenggunaController::class, 'index'])->name('pengguna')->whereIn('role', ['guru', 'siswa']);
    Route::post('/pengguna/{role}', [PenggunaController::class, 'simpan'])->whereIn('role', ['guru', 'siswa']);
    Route::post('/pengguna/{role}/impor', [PenggunaController::class, 'impor'])->name('pengguna.impor')->whereIn('role', ['guru', 'siswa']);
    Route::get('/pengguna/{role}/contoh-csv', [PenggunaController::class, 'contohCsv'])->name('pengguna.contoh')->whereIn('role', ['guru', 'siswa']);
    Route::post('/pengguna/{user}/reset', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset');
    Route::delete('/pengguna/{user}', [PenggunaController::class, 'hapus'])->name('pengguna.hapus');
});
