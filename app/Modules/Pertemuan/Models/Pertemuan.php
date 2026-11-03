<?php

namespace App\Modules\Pertemuan\Models;

use App\Modules\Ruang\Models\Ruang;
use App\Modules\Tugas\Models\Tugas;
use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    protected $table = 'pertemuan';
    protected $fillable = ['ruang_id', 'urutan', 'judul', 'isi', 'video_url', 'lampiran_path', 'lampiran_nama', 'tanggal', 'presensi_buka', 'presensi_tutup', 'terbit'];
    protected $casts = ['tanggal' => 'date', 'presensi_buka' => 'datetime', 'presensi_tutup' => 'datetime', 'terbit' => 'boolean'];

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    public function presensiTerbuka(): bool
    {
        return $this->presensi_buka && $this->presensi_tutup && now()->between($this->presensi_buka, $this->presensi_tutup);
    }

    /** ID video YouTube dari berbagai bentuk link (watch, youtu.be, shorts, embed) */
    public function youtubeId(): ?string
    {
        if (!$this->video_url) return null;
        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $this->video_url, $m);
        return $m[1] ?? null;
    }
}
