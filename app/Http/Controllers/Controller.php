<?php

namespace App\Http\Controllers;

use App\Models\Ruang;

abstract class Controller
{
    /** Pastikan data (pertemuan/tugas/kuis/...) memang milik ruang belajar di URL */
    protected function milik($model, Ruang $ruang): void
    {
        abort_unless((int) $model->ruang_id === (int) $ruang->id, 404);
    }
}
