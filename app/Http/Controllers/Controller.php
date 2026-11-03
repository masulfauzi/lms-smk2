<?php

namespace App\Http\Controllers;

use App\Modules\Ruang\Models\Ruang;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /** Pastikan data (pertemuan/tugas/kuis/...) memang milik ruang belajar di URL */
    protected function milik($model, Ruang $ruang): void
    {
        abort_unless((int) $model->ruang_id === (int) $ruang->id, 404);
    }

    /** Kirim berkas dari storage privat: PDF & gambar dibuka di browser, lainnya diunduh */
    protected function kirimBerkas(?string $path, ?string $nama)
    {
        abort_unless($path && Storage::exists($path), 404, 'Berkas tidak ditemukan.');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)
            ? Storage::response($path, $nama)
            : Storage::download($path, $nama);
    }
}
