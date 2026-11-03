<?php

use App\Modules\Mapel\Controllers\MapelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/mapel', [MapelController::class, 'index'])->name('mapel');
    Route::post('/mapel', [MapelController::class, 'simpan']);
    Route::delete('/mapel/{mapel}', [MapelController::class, 'hapus'])->name('mapel.hapus');
});
