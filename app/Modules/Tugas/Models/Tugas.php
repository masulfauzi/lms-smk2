<?php

namespace App\Modules\Tugas\Models;

use App\Modules\Ruang\Models\Ruang;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';
    protected $fillable = ['ruang_id', 'pertemuan_id', 'judul', 'instruksi', 'lampiran_path', 'lampiran_nama', 'tenggat', 'jenis_kumpul', 'boleh_terlambat'];
    protected $casts = ['tenggat' => 'datetime', 'boleh_terlambat' => 'boolean'];

    public const JENIS = ['keduanya' => 'File atau teks', 'file' => 'Hanya file', 'teks' => 'Hanya teks'];

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }

    public function pengumpulan()
    {
        return $this->hasMany(Pengumpulan::class);
    }

    public function lewatTenggat(): bool
    {
        return now()->greaterThan($this->tenggat);
    }

    public function bisaDikumpulkan(): bool
    {
        return !$this->lewatTenggat() || $this->boleh_terlambat;
    }
}
