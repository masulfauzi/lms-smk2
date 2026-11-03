<?php

namespace App\Modules\Jurusan\Models;

use App\Modules\Kelas\Models\Kelas;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $fillable = ['kode', 'nama'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}
