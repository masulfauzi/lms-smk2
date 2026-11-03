<?php

namespace App\Support;

use App\Models\Kuis;
use App\Models\Percobaan;
use App\Models\Pengumpulan;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Ruang;
use App\Models\Tugas;

/**
 * Rekap nilai satu ruang belajar.
 * - Tugas: dihitung bila sudah dinilai, atau tenggat lewat tanpa pengumpulan (nilai 0).
 *   Tugas yang sudah dikumpulkan tapi belum dinilai belum ikut dihitung.
 * - Kuis: dihitung bila sudah ada nilai, atau kuis sudah ditutup tanpa dikerjakan (nilai 0).
 * - Kehadiran: hadir / (pertemuan yang presensinya sudah ditutup - izin - sakit).
 *   Siswa tanpa catatan pada presensi yang sudah tutup dihitung alpa.
 * - Nilai akhir: rata-rata berbobot sesuai pengaturan ruang (bobot dinormalkan bila
 *   salah satu komponen belum punya data).
 */
class Rapor
{
    public static function ruang(Ruang $ruang): array
    {
        $siswa = $ruang->siswa()->get(['id', 'name', 'username']);
        $ids = $siswa->pluck('id');

        $tugas = Tugas::where('ruang_id', $ruang->id)->orderBy('tenggat')->get();
        $kumpul = Pengumpulan::whereIn('tugas_id', $tugas->pluck('id'))->get()->groupBy('siswa_id');

        $kuis = Kuis::where('ruang_id', $ruang->id)->where('terbit', true)->orderBy('mulai')->get();
        $coba = Percobaan::whereIn('kuis_id', $kuis->pluck('id'))->whereNotNull('selesai_at')->get()->groupBy('siswa_id');

        $pertemuanTutup = Pertemuan::where('ruang_id', $ruang->id)->whereNotNull('presensi_tutup')
            ->where('presensi_tutup', '<', now())->pluck('id');
        $presensi = Presensi::whereIn('pertemuan_id', $pertemuanTutup)->get()->groupBy('siswa_id');

        $baris = [];
        foreach ($siswa as $s) {
            $k = ($kumpul[$s->id] ?? collect())->keyBy('tugas_id');
            $nt = [];
            foreach ($tugas as $t) {
                $p = $k[$t->id] ?? null;
                if ($p && $p->nilai !== null) $nt[$t->id] = (int) $p->nilai;
                elseif (!$p && $t->lewatTenggat()) $nt[$t->id] = 0;
                else $nt[$t->id] = null; // belum tenggat / belum dinilai
            }

            $c = ($coba[$s->id] ?? collect())->keyBy('kuis_id');
            $nk = [];
            foreach ($kuis as $q) {
                $p = $c[$q->id] ?? null;
                if ($p && $p->nilai !== null) $nk[$q->id] = (float) $p->nilai;
                elseif (!$p && now()->gt($q->selesai)) $nk[$q->id] = 0;
                else $nk[$q->id] = null;
            }

            $pr = ($presensi[$s->id] ?? collect())->countBy('status');
            $hadir = $pr['hadir'] ?? 0;
            $izinSakit = ($pr['izin'] ?? 0) + ($pr['sakit'] ?? 0);
            $wajib = $pertemuanTutup->count() - $izinSakit;
            $alpa = max(0, $wajib - $hadir);
            $persenHadir = $wajib > 0 ? round($hadir / $wajib * 100, 1) : null;

            $rataTugas = self::rata($nt);
            $rataKuis = self::rata($nk);
            $baris[] = [
                'siswa' => $s,
                'tugas' => $nt,
                'kuis' => $nk,
                'rata_tugas' => $rataTugas,
                'rata_kuis' => $rataKuis,
                'hadir' => $hadir,
                'izin_sakit' => $izinSakit,
                'alpa' => $alpa,
                'persen_hadir' => $persenHadir,
                'akhir' => self::akhir($ruang, $rataTugas, $rataKuis, $persenHadir),
            ];
        }

        return ['tugas' => $tugas, 'kuis' => $kuis, 'baris' => $baris, 'pertemuan_tutup' => $pertemuanTutup->count()];
    }

    private static function rata(array $nilai): ?float
    {
        $ada = array_filter($nilai, fn ($n) => $n !== null);
        return $ada ? round(array_sum($ada) / count($ada), 1) : null;
    }

    public static function akhir(Ruang $r, ?float $tugas, ?float $kuis, ?float $hadir): ?float
    {
        $komponen = [[$tugas, $r->bobot_tugas], [$kuis, $r->bobot_kuis], [$hadir, $r->bobot_kehadiran]];
        $bobot = 0;
        $jumlah = 0;
        foreach ($komponen as [$n, $b]) {
            if ($n === null || $b <= 0) continue;
            $jumlah += $n * $b;
            $bobot += $b;
        }
        return $bobot ? round($jumlah / $bobot, 1) : null;
    }
}
