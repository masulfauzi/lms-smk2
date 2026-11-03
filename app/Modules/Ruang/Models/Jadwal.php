<?php

namespace App\Modules\Ruang\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $fillable = ['ruang_id', 'hari', 'jam_mulai', 'jam_selesai', 'link_meet'];

    public const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }
}
