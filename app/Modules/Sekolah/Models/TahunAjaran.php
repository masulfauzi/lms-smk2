<?php

namespace App\Modules\Sekolah\Models;

use App\Modules\Ruang\Models\Ruang;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $fillable = ['nama', 'semester', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];

    private static ?int $aktif = null;

    public static function aktifId(): int
    {
        return self::$aktif ??= (int) static::where('aktif', true)->value('id');
    }

    public static function aktif(): ?self
    {
        return static::find(static::aktifId());
    }

    public function ruang()
    {
        return $this->hasMany(Ruang::class);
    }

    public function label(): string
    {
        return $this->nama . ' ' . $this->semester;
    }
}
