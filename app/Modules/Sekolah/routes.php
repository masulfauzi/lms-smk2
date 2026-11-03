<?php

use App\Modules\Sekolah\Controllers\SekolahController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/sekolah', [SekolahController::class, 'index'])->name('sekolah');
    Route::post('/sekolah', [SekolahController::class, 'simpan']);
    Route::post('/tahun-ajaran', [SekolahController::class, 'tambahTahun'])->name('tahun.store');
    Route::post('/tahun-ajaran/{tahun}/aktif', [SekolahController::class, 'aktifkanTahun'])->name('tahun.aktif');
});
