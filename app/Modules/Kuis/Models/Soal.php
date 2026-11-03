<?php

namespace App\Modules\Kuis\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';
    protected $fillable = ['kuis_id', 'urutan', 'jenis', 'pertanyaan', 'opsi', 'kunci', 'bobot'];
    protected $casts = ['opsi' => 'array'];
}
