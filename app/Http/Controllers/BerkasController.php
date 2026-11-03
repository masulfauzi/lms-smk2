<?php

namespace App\Http\Controllers;

use App\Models\Pengumpulan;
use App\Models\Pertemuan;
use App\Models\Ruang;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Semua berkas disimpan di storage privat dan hanya diunduh lewat pengecekan hak akses */
class BerkasController extends Controller
{
    public function pertemuan(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        abort_if($r->user()->role === 'siswa' && !$pertemuan->terbit, 404);
        return $this->kirim($pertemuan->lampiran_path, $pertemuan->lampiran_nama);
    }

    public function tugas(Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        return $this->kirim($tugas->lampiran_path, $tugas->lampiran_nama);
    }

    public function pengumpulan(Request $r, Ruang $ruang, Pengumpulan $pengumpulan)
    {
        $pengumpulan->load('tugas');
        $this->milik($pengumpulan->tugas, $ruang);
        $u = $r->user();
        abort_if($u->role === 'siswa' && $pengumpulan->siswa_id !== $u->id, 403);
        return $this->kirim($pengumpulan->file_path, $pengumpulan->file_nama);
    }

    private function kirim(?string $path, ?string $nama)
    {
        abort_unless($path && Storage::exists($path), 404, 'Berkas tidak ditemukan.');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        // PDF & gambar dibuka di browser, lainnya diunduh
        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)
            ? Storage::response($path, $nama)
            : Storage::download($path, $nama);
    }
}
