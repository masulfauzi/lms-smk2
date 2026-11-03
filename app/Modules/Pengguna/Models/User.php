<?php

namespace App\Modules\Pengguna\Models;

use App\Modules\Kelas\Models\Kelas;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Sekolah\Models\TahunAjaran;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'role', 'jk', 'kelas_id', 'aktif', 'password', 'terakhir_aktif_at'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean', 'terakhir_aktif_at' => 'datetime'];
    }

    public const ROLES = ['admin' => 'Admin Sekolah', 'guru' => 'Guru', 'siswa' => 'Siswa'];

    public function punya(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /** Ruang belajar yang bisa diakses user ini pada tahun ajaran aktif */
    public function ruangQuery()
    {
        $q = Ruang::query()->where('tahun_ajaran_id', TahunAjaran::aktifId());
        return match ($this->role) {
            'guru' => $q->where('guru_id', $this->id),
            'siswa' => $q->where('kelas_id', $this->kelas_id ?? 0),
            default => $q,
        };
    }

    public function bolehAkses(Ruang $ruang): bool
    {
        return match ($this->role) {
            'admin' => true,
            'guru' => $ruang->guru_id === $this->id,
            'siswa' => $ruang->kelas_id === $this->kelas_id,
            default => false,
        };
    }

    public function inisial(): string
    {
        $k = preg_split('/\s+/', trim($this->name));
        return strtoupper(mb_substr($k[0] ?? '', 0, 1) . mb_substr($k[1] ?? '', 0, 1));
    }
}
