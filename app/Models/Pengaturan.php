<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $primaryKey = 'kunci';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['kunci', 'nilai'];

    public const BAWAAN = [
        'nama_sekolah' => 'SMK Nusantara Cendekia',
        'jenjang' => 'SMK',
        'npsn' => '20123456',
        'alamat' => 'Jl. Pendidikan No. 17, Yogyakarta',
        'kepala_sekolah' => 'Dra. Sri Wahyuni, M.Pd.',
        'kkm' => '75',
    ];

    public static function ambil(string $kunci): string
    {
        $semua = Cache::rememberForever('pengaturan', fn () => static::pluck('nilai', 'kunci')->all());
        return (string) ($semua[$kunci] ?? self::BAWAAN[$kunci] ?? '');
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $k => $v) {
            static::updateOrCreate(['kunci' => $k], ['nilai' => $v]);
        }
        Cache::forget('pengaturan');
    }
}
