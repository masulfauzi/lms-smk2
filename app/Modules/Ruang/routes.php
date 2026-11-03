<?php

use App\Modules\Ruang\Controllers\PembagianController;
use App\Modules\Ruang\Controllers\RuangController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('/ruang', [RuangController::class, 'index'])->name('ruang');

    // Hak akses dicek per ruang: guru pengampu, siswa kelas tsb, atau admin.
    Route::prefix('ruang/{ruang}')->middleware('can:akses,ruang')->group(function () {
        Route::get('/', [RuangController::class, 'show'])->name('ruang.show');
        Route::get('/anggota', [RuangController::class, 'anggota'])->name('ruang.anggota');
        Route::get('/presensi', [RuangController::class, 'presensi'])->name('ruang.presensi');
        Route::get('/nilai', [RuangController::class, 'nilai'])->name('ruang.nilai');
        Route::get('/nilai/csv', [RuangController::class, 'nilaiCsv'])->name('ruang.nilai.csv')->middleware('role:admin,guru');

        Route::middleware('role:admin,guru')->group(function () {
            Route::get('/pengaturan', [RuangController::class, 'pengaturan'])->name('ruang.pengaturan');
            Route::post('/pengaturan', [RuangController::class, 'simpanPengaturan']);
        });
    });

    // Pembagian mengajar & jadwal (admin)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/ruang', [PembagianController::class, 'index'])->name('ruang');
        Route::post('/ruang', [PembagianController::class, 'simpan']);
        Route::delete('/ruang/{ruang}', [PembagianController::class, 'hapus'])->name('ruang.hapus');
        Route::post('/ruang/{ruang}/jadwal', [PembagianController::class, 'tambahJadwal'])->name('ruang.jadwal');
        Route::delete('/jadwal/{jadwal}', [PembagianController::class, 'hapusJadwal'])->name('jadwal.hapus');
    });
});
