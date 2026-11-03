<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';
    protected $fillable = ['ruang_id', 'judul', 'petunjuk', 'mulai', 'selesai', 'durasi_menit', 'acak_soal', 'acak_opsi', 'tampilkan_nilai', 'terbit'];
    protected $casts = ['mulai' => 'datetime', 'selesai' => 'datetime', 'acak_soal' => 'boolean', 'acak_opsi' => 'boolean', 'tampilkan_nilai' => 'boolean', 'terbit' => 'boolean'];

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }

    public function soal()
    {
        return $this->hasMany(Soal::class)->orderBy('urutan');
    }

    public function percobaan()
    {
        return $this->hasMany(Percobaan::class);
    }

    public function status(): string
    {
        if (!$this->terbit) return 'draf';
        if (now()->lt($this->mulai)) return 'belum';
        if (now()->gt($this->selesai)) return 'selesai';
        return 'berlangsung';
    }

    public function totalBobot(): int
    {
        return (int) $this->soal->sum('bobot');
    }
}
