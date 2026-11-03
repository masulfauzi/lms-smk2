<?php

use App\Modules\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/jadwal', [DashboardController::class, 'jadwal'])->name('jadwal');
});
