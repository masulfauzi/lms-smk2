<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';
    protected $fillable = ['user_id', 'judul', 'isi', 'sasaran', 'kelas_id', 'disematkan'];
    protected $casts = ['disematkan' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /** Pengumuman yang boleh dilihat user */
    public static function untuk(User $u)
    {
        $q = static::query()->with('user')->orderByDesc('disematkan')->latest();
        if ($u->role === 'admin') return $q;
        return $q->where(fn ($w) => $w->where('user_id', $u->id)->orWhere(
            fn ($x) => $x->whereIn('sasaran', ['semua', $u->role])
                ->where(fn ($k) => $k->whereNull('kelas_id')->when($u->role === 'siswa', fn ($y) => $y->orWhere('kelas_id', $u->kelas_id)))
        ));
    }
}
