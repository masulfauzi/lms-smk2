<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Ruang;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\Rapor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RekapController extends Controller
{
    private function kelasDipilih(Request $r)
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        return [$kelasList, $kelasList->firstWhere('id', (int) $r->query('kelas')) ?? $kelasList->first()];
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
        return view('admin.rekap-presensi', compact('kelasList', 'kelas', 'bulan', 'rekap', 'pertemuan'));
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
        return view('admin.rekap-nilai', compact('kelasList', 'kelas', 'ruang', 'siswa', 'nilai'));
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
        return view('admin.rekap-keaktifan', compact('kelasList', 'kelas', 'siswa'));
    }
}
