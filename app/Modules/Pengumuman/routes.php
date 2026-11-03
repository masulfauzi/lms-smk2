<?php

use App\Modules\Pengumuman\Controllers\PengumumanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->middleware('role:admin,guru');
    Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.hapus')->middleware('role:admin,guru');
});
