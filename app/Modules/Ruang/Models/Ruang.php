<?php

namespace App\Modules\Ruang\Models;

use App\Modules\Kelas\Models\Kelas;
use App\Modules\Kuis\Models\Kuis;
use App\Modules\Mapel\Models\Mapel;
use App\Modules\Pengguna\Models\User;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Tugas\Models\Tugas;
use Illuminate\Database\Eloquent\Model;

/** Ruang belajar: satu mapel di satu kelas yang diampu satu guru */
class Ruang extends Model
{
    protected $table = 'ruang';
    protected $fillable = ['tahun_ajaran_id', 'kelas_id', 'mapel_id', 'guru_id', 'deskripsi', 'kkm', 'bobot_tugas', 'bobot_kuis', 'bobot_kehadiran'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class)->orderBy('urutan');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class)->orderByDesc('tenggat');
    }

    public function kuis()
    {
        return $this->hasMany(Kuis::class)->orderByDesc('mulai');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class)->orderBy('hari')->orderBy('jam_mulai');
    }

    public function siswa()
    {
        return User::where('role', 'siswa')->where('kelas_id', $this->kelas_id)->where('aktif', true)->orderBy('name');
    }

    public function judul(): string
    {
        return $this->mapel->nama . ' · ' . $this->kelas->nama;
    }

    /** Props header ruang belajar untuk komponen Vue RuangKepala (dipakai halaman di beberapa modul) */
    public function kepala(): array
    {
        $this->loadMissing('mapel', 'kelas', 'guru');
        return [
            'id' => $this->id,
            'judul' => $this->judul(),
            'mapel' => $this->mapel->nama,
            'kelas' => $this->kelas->nama,
            'guru' => $this->guru?->name,
            'kkm' => $this->kkm,
            'warna' => $this->warna(),
        ];
    }

    /** Warna kartu tetap per mapel (dari palet terbatas) */
    public function warna(): string
    {
        $palet = ['#2f5bd3', '#d4553a', '#1f8a5b', '#b07a10', '#7a4fc9', '#c23d7a', '#2a8a9a', '#5b6b2a'];
        return $palet[$this->mapel_id % count($palet)];
    }
}
