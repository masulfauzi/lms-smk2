<?php

use App\Modules\Diskusi\Controllers\DiskusiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'can:akses,ruang'])->prefix('ruang/{ruang}')->group(function () {
    Route::get('/diskusi', [DiskusiController::class, 'index'])->name('ruang.diskusi');
    Route::post('/diskusi', [DiskusiController::class, 'store']);
    Route::delete('/diskusi/{diskusi}', [DiskusiController::class, 'destroy'])->name('diskusi.hapus');
});
