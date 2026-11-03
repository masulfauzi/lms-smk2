<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['tingkat', 'jurusan_id', 'nama', 'wali_id'];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function wali()
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    public function siswa()
    {
        return $this->hasMany(User::class)->where('role', 'siswa')->orderBy('name');
    }

    public function ruang()
    {
        return $this->hasMany(Ruang::class);
    }

    /** Rentang tingkat sesuai jenjang sekolah */
    public static function tingkatJenjang(?string $jenjang = null): array
    {
        return match ($jenjang ?? setting('jenjang')) {
            'SD' => range(1, 6),
            'SMP' => range(7, 9),
            default => range(10, 12),
        };
    }
}
