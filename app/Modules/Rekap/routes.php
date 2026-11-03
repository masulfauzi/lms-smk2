<?php

use App\Modules\Rekap\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/rekap/presensi', [RekapController::class, 'presensi'])->name('rekap.presensi');
    Route::get('/rekap/nilai', [RekapController::class, 'nilai'])->name('rekap.nilai');
    Route::get('/rekap/keaktifan', [RekapController::class, 'keaktifan'])->name('rekap.keaktifan');
});
