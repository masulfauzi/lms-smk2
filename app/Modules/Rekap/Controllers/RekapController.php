<?php

namespace App\Modules\Rekap\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kelas\Models\Kelas;
use App\Modules\Pengguna\Models\User;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Pertemuan\Models\Presensi;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Sekolah\Models\TahunAjaran;
use App\Support\Rapor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class RekapController extends Controller
{
    private function kelasDipilih(Request $r)
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        return [$kelasList, $kelasList->firstWhere('id', (int) $r->query('kelas')) ?? $kelasList->first()];
    }

    /** Props bersama: daftar kelas untuk komponen PilihKelas.vue */
    private function propsKelas($kelasList, $kelas): array
    {
        return ['kelasList' => $kelasList->map(fn ($k) => $k->only('id', 'nama')), 'kelas' => $kelas?->only('id', 'nama')];
    }

    /** Rekap presensi per kelas per bulan, semua mapel digabung */
    public function presensi(Request $r)
    {
        [$kelasList, $kelas] = $this->kelasDipilih($r);
        $bulan = Carbon::parse(($r->query('bulan') ?: now()->format('Y-m')) . '-01');
        $siswa = $kelas ? $kelas->siswa()->where('aktif', true)->get() : collect();
        $pertemuan = $kelas ? Pertemuan::whereHas('ruang', fn ($q) => $q->where('kelas_id', $kelas->id)->where('tahun_ajaran_id', TahunAjaran::aktifId()))
            ->whereNotNull('presensi_tutup')->where('presensi_tutup', '<', now())
            ->whereBetween('tanggal', [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()])->pluck('id') : collect();
        $data = Presensi::whereIn('pertemuan_id', $pertemuan)->get()->groupBy('siswa_id');
        $rekap = $siswa->map(function ($s) use ($data, $pertemuan) {
            $c = ($data[$s->id] ?? collect())->countBy('status');
            $tercatat = $c->sum();
            return ['siswa' => $s, 'hadir' => $c['hadir'] ?? 0, 'izin' => $c['izin'] ?? 0, 'sakit' => $c['sakit'] ?? 0,
                'alpa' => ($c['alpa'] ?? 0) + max(0, $pertemuan->count() - $tercatat)];
        });
        return Inertia::render('Rekap/Presensi', $this->propsKelas($kelasList, $kelas) + [
            'bulan' => $bulan->format('Y-m'),
            'bulanLabel' => $bulan->locale('id')->translatedFormat('F Y'),
            'jumlahPertemuan' => $pertemuan->count(),
            'rekap' => $rekap->map(function ($b) {
                $wajib = $b['hadir'] + $b['alpa'];
                return ['id' => $b['siswa']->id, 'nama' => $b['siswa']->name, 'nisn' => $b['siswa']->username,
                    'hadir' => $b['hadir'], 'izin' => $b['izin'], 'sakit' => $b['sakit'], 'alpa' => $b['alpa'],
                    'persen' => $wajib ? round($b['hadir'] / $wajib * 100) : null];
            })->values(),
        ]);
    }

    /** Leger nilai: nilai akhir semua mapel per siswa dalam satu kelas */
    public function nilai(Request $r)
    {
        [$kelasList, $kelas] = $this->kelasDipilih($r);
        $ruang = $kelas ? Ruang::with('mapel', 'kelas')->where('kelas_id', $kelas->id)->where('tahun_ajaran_id', TahunAjaran::aktifId())
            ->get()->sortBy('mapel.nama')->values() : collect();
        $siswa = $kelas ? $kelas->siswa()->where('aktif', true)->get() : collect();
        $nilai = [];
        foreach ($ruang as $ru) {
            foreach (Rapor::ruang($ru)['baris'] as $b) $nilai[$b['siswa']->id][$ru->id] = $b['akhir'];
        }
        if ($r->query('csv')) return $this->csvLeger($kelas, $ruang, $siswa, $nilai);
        $rata = [];
        foreach ($siswa as $s) {
            $ada = array_filter($nilai[$s->id] ?? [], fn ($n) => $n !== null);
            $rata[$s->id] = $ada ? round(array_sum($ada) / count($ada), 1) : null;
        }
        $urut = collect($rata)->filter(fn ($n) => $n !== null)->sortDesc()->keys()->flip();
        return Inertia::render('Rekap/Nilai', $this->propsKelas($kelasList, $kelas) + [
            'tahunAjaran' => TahunAjaran::aktif()?->label(),
            'ruang' => $ruang->map(fn ($ru) => ['id' => $ru->id, 'kode' => $ru->mapel->kode, 'mapel' => $ru->mapel->nama, 'kkm' => $ru->kkm]),
            'siswa' => $siswa->map(fn ($s) => [
                'id' => $s->id, 'nama' => $s->name,
                'nilai' => $ruang->mapWithKeys(fn ($ru) => [$ru->id => $nilai[$s->id][$ru->id] ?? null]),
                'rata' => $rata[$s->id], 'rank' => isset($urut[$s->id]) ? $urut[$s->id] + 1 : null,
            ])->values(),
        ]);
    }

    private function csvLeger($kelas, $ruang, $siswa, $nilai)
    {
        $sel = fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"';
        $kepala = array_merge(['No', 'NISN', 'Nama'], $ruang->map(fn ($r) => $r->mapel->nama)->all(), ['Rata-rata']);
        $out = [implode(';', array_map($sel, $kepala))];
        foreach ($siswa as $i => $s) {
            $row = [$i + 1, $s->username, $s->name];
            $ada = [];
            foreach ($ruang as $ru) {
                $n = $nilai[$s->id][$ru->id] ?? null;
                $row[] = $n ?? '';
                if ($n !== null) $ada[] = $n;
            }
            $row[] = $ada ? round(array_sum($ada) / count($ada), 1) : '';
            $out[] = implode(';', array_map($sel, $row));
        }
        return response("\u{FEFF}" . implode("\r\n", $out), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="leger-' . str($kelas->nama)->slug() . '.csv"',
        ]);
    }

    /** Pantauan keaktifan siswa: kapan terakhir membuka LMS */
    public function keaktifan(Request $r)
    {
        [$kelasList, $kelas] = $this->kelasDipilih($r);
        $siswa = $kelas ? User::where('role', 'siswa')->where('kelas_id', $kelas->id)->where('aktif', true)
            ->orderByRaw('terakhir_aktif_at is null desc')->orderBy('terakhir_aktif_at')->get() : collect();
        $batas = now()->subDays(3);
        $lama = $siswa->filter(fn ($s) => !$s->terakhir_aktif_at || $s->terakhir_aktif_at->lt($batas))->count();
        return Inertia::render('Rekap/Keaktifan', $this->propsKelas($kelasList, $kelas) + [
            'stat' => [
                'siswa' => $siswa->count(),
                'hari_ini' => $siswa->filter(fn ($s) => $s->terakhir_aktif_at?->isToday())->count(),
                'aktif' => $siswa->count() - $lama,
                'lama' => $lama,
            ],
            'siswa' => $siswa->values()->map(function ($s) use ($batas) {
                $t = $s->terakhir_aktif_at;
                return [
                    'id' => $s->id, 'nama' => $s->name, 'nisn' => $s->username,
                    'terakhir' => $t ? tgl($t, true) . ' (' . $t->locale('id')->diffForHumans() . ')' : 'Belum pernah masuk',
                    'status' => !$t ? 'belum' : ($t->lt($batas) ? 'lama' : ($t->isToday() ? 'hari_ini' : 'aktif')),
                ];
            }),
        ]);
    }
}
