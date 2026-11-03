<?php

namespace App\Modules\Pertemuan\Models;

use App\Modules\Pengguna\Models\User;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';
    protected $fillable = ['pertemuan_id', 'siswa_id', 'status', 'waktu', 'keterangan', 'oleh_guru'];
    protected $casts = ['waktu' => 'datetime', 'oleh_guru' => 'boolean'];

    public const STATUS = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class);
    }
}
