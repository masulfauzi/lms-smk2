<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Percobaan extends Model
{
    protected $table = 'percobaan';
    protected $fillable = ['kuis_id', 'siswa_id', 'mulai_at', 'batas_at', 'selesai_at', 'urutan_soal', 'jawaban', 'nilai_esai', 'nilai'];
    protected $casts = [
        'mulai_at' => 'datetime', 'batas_at' => 'datetime', 'selesai_at' => 'datetime',
        'urutan_soal' => 'array', 'jawaban' => 'array', 'nilai_esai' => 'array',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class);
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function sedangBerjalan(): bool
    {
        return !$this->selesai_at && now()->lt($this->batas_at);
    }

    /**
     * Hitung nilai 0-100. PG dinilai otomatis dari kunci; esai memakai skor dari guru.
     * Mengembalikan null bila masih ada esai yang belum dikoreksi.
     */
    public function hitungNilai(): ?float
    {
        $soal = $this->kuis->soal;
        $total = max(1, (int) $soal->sum('bobot'));
        $jawab = $this->jawaban ?? [];
        $esai = $this->nilai_esai ?? [];
        $skor = 0;
        foreach ($soal as $s) {
            if ($s->jenis === 'pg') {
                if (($jawab[$s->id] ?? null) === $s->kunci) $skor += $s->bobot;
            } else {
                if (!array_key_exists($s->id, $esai)) return null;
                $skor += min($s->bobot, max(0, (float) $esai[$s->id]));
            }
        }
        return round($skor / $total * 100, 2);
    }
}
