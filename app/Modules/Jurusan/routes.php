<?php

use App\Modules\Jurusan\Controllers\JurusanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan');
    Route::post('/jurusan', [JurusanController::class, 'simpan']);
    Route::delete('/jurusan/{jurusan}', [JurusanController::class, 'hapus'])->name('jurusan.hapus');
});
