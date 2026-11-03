<?php

namespace App\Modules\Ruang\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Ruang\Models\Jadwal;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Pertemuan\Models\Presensi;
use App\Modules\Ruang\Models\Ruang;
use App\Support\Rapor;
use Illuminate\Http\Request;

class RuangController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $q = $u->ruangQuery()->with('mapel', 'kelas', 'guru')->withCount('pertemuan');
        if ($u->role === 'admin' && $r->filled('kelas')) $q->where('kelas_id', $r->query('kelas'));
        $ruang = $q->get()->sortBy(fn ($x) => sprintf('%02d%s%s', $x->kelas->tingkat, $x->kelas->nama, $x->mapel->nama))->values();
        return view('ruang::index', compact('ruang'));
    }

    /** Halaman utama ruang: daftar pertemuan (materi) dari terbaru */
    public function show(Request $r, Ruang $ruang)
    {
        $u = $r->user();
        $ruang->load('mapel', 'kelas', 'guru', 'jadwal');
        $pertemuan = Pertemuan::where('ruang_id', $ruang->id)
            ->when($u->role === 'siswa', fn ($q) => $q->where('terbit', true))
            ->withCount(['presensi as hadir_count' => fn ($q) => $q->where('status', 'hadir')])
            ->orderByDesc('urutan')->get();

        $presensiSaya = $selesaiSaya = collect();
        if ($u->role === 'siswa') {
            $presensiSaya = Presensi::where('siswa_id', $u->id)->whereIn('pertemuan_id', $pertemuan->pluck('id'))->pluck('status', 'pertemuan_id');
            $selesaiSaya = \DB::table('materi_selesai')->where('siswa_id', $u->id)->whereIn('pertemuan_id', $pertemuan->pluck('id'))->pluck('pertemuan_id')->flip();
        }
        $jumlahSiswa = $ruang->siswa()->count();
        return view('ruang::show', compact('ruang', 'pertemuan', 'presensiSaya', 'selesaiSaya', 'jumlahSiswa'));
    }

    public function anggota(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas.wali', 'guru');
        $siswa = $ruang->siswa()->get();
        return view('ruang::anggota', compact('ruang', 'siswa'));
    }

    /** Rekap presensi: siswa x pertemuan */
    public function presensi(Request $r, Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $u = $r->user();
        $pertemuan = Pertemuan::where('ruang_id', $ruang->id)->where('terbit', true)->whereNotNull('presensi_buka')->orderBy('urutan')->get();
        $siswa = $u->role === 'siswa' ? collect([$u]) : $ruang->siswa()->get();
        $data = Presensi::whereIn('pertemuan_id', $pertemuan->pluck('id'))->whereIn('siswa_id', $siswa->pluck('id'))->get()
            ->groupBy('siswa_id')->map(fn ($g) => $g->keyBy('pertemuan_id'));
        return view('ruang::presensi', compact('ruang', 'pertemuan', 'siswa', 'data'));
    }

    public function nilai(Request $r, Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $rapor = Rapor::ruang($ruang);
        if ($r->user()->role === 'siswa') {
            $rapor['baris'] = array_values(array_filter($rapor['baris'], fn ($b) => $b['siswa']->id === $r->user()->id));
        }
        return view('ruang::nilai', compact('ruang', 'rapor'));
    }

    public function nilaiCsv(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $rapor = Rapor::ruang($ruang);
        $kepala = ['No', 'NISN', 'Nama'];
        foreach ($rapor['tugas'] as $i => $t) $kepala[] = 'Tugas ' . ($i + 1);
        foreach ($rapor['kuis'] as $i => $k) $kepala[] = 'Kuis ' . ($i + 1);
        array_push($kepala, 'Rata Tugas', 'Rata Kuis', 'Hadir', 'Izin/Sakit', 'Alpa', '% Hadir', 'Nilai Akhir', 'Predikat', 'Tuntas (KKM ' . $ruang->kkm . ')');

        $sel = fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"';
        $baris = [implode(';', array_map($sel, $kepala))];
        foreach ($rapor['baris'] as $i => $b) {
            $row = [$i + 1, $b['siswa']->username, $b['siswa']->name];
            foreach ($b['tugas'] as $n) $row[] = $n ?? '';
            foreach ($b['kuis'] as $n) $row[] = $n ?? '';
            array_push($row, $b['rata_tugas'] ?? '', $b['rata_kuis'] ?? '', $b['hadir'], $b['izin_sakit'], $b['alpa'],
                $b['persen_hadir'] ?? '', $b['akhir'] ?? '', predikat($b['akhir']), $b['akhir'] === null ? '' : ($b['akhir'] >= $ruang->kkm ? 'Ya' : 'Belum'));
            $baris[] = implode(';', array_map($sel, $row));
        }
        $nama = 'nilai-' . str($ruang->mapel->nama . '-' . $ruang->kelas->nama)->slug() . '.csv';
        return response("\u{FEFF}" . implode("\r\n", $baris), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    public function pengaturan(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas', 'jadwal');
        return view('ruang::pengaturan', compact('ruang'));
    }

    public function simpanPengaturan(Request $r, Ruang $ruang)
    {
        $data = $r->validate([
            'deskripsi' => 'nullable|string|max:2000',
            'kkm' => 'required|integer|min:0|max:100',
            'bobot_tugas' => 'required|integer|min:0|max:100',
            'bobot_kuis' => 'required|integer|min:0|max:100',
            'bobot_kehadiran' => 'required|integer|min:0|max:100',
            'link' => 'array',
            'link.*' => 'nullable|url|max:255',
        ]);
        if ($data['bobot_tugas'] + $data['bobot_kuis'] + $data['bobot_kehadiran'] !== 100) {
            return back()->withInput()->with('error', 'Jumlah bobot tugas, kuis, dan kehadiran harus 100%.');
        }
        $ruang->update(collect($data)->except('link')->all());
        foreach ($data['link'] ?? [] as $jadwalId => $link) {
            Jadwal::where('ruang_id', $ruang->id)->whereKey($jadwalId)->update(['link_meet' => $link]);
        }
        return back()->with('ok', 'Pengaturan ruang belajar disimpan.');
    }
}
