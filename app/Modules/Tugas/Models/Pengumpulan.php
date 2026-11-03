<?php

namespace App\Modules\Tugas\Models;

use App\Modules\Pengguna\Models\User;
use Illuminate\Database\Eloquent\Model;

class Pengumpulan extends Model
{
    protected $table = 'pengumpulan';
    protected $fillable = ['tugas_id', 'siswa_id', 'teks', 'file_path', 'file_nama', 'dikumpulkan_at', 'terlambat', 'nilai', 'komentar', 'dinilai_at'];
    protected $casts = ['dikumpulkan_at' => 'datetime', 'dinilai_at' => 'datetime', 'terlambat' => 'boolean'];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
