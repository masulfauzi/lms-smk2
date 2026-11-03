<?php

namespace App\Modules\Ruang\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Pertemuan\Models\Presensi;
use App\Modules\Ruang\Models\Jadwal;
use App\Modules\Ruang\Models\Ruang;
use App\Support\Rapor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RuangController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $q = $u->ruangQuery()->with('mapel', 'kelas', 'guru')->withCount('pertemuan');
        if ($u->role === 'admin' && $r->filled('kelas')) $q->where('kelas_id', $r->query('kelas'));
        $ruang = $q->get()->sortBy(fn ($x) => sprintf('%02d%s%s', $x->kelas->tingkat, $x->kelas->nama, $x->mapel->nama))->values();
        return Inertia::render('Ruang/Index', [
            'kelasList' => $u->role === 'admin' ? \App\Modules\Kelas\Models\Kelas::orderBy('tingkat')->orderBy('nama')->get()->map(fn ($k) => $k->only('id', 'nama')) : [],
            'kelasId' => $r->query('kelas', ''),
            'ruang' => $ruang->map(fn ($x) => [
                'id' => $x->id, 'mapel' => $x->mapel->nama, 'warna' => $x->warna(), 'pertemuan_count' => $x->pertemuan_count,
                'keterangan' => $u->role === 'siswa' ? ($x->guru?->name ?? '-') : $x->kelas->nama . ' · ' . ($x->guru?->name ?? 'Belum ada guru'),
            ]),
        ]);
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
        $siswa = $u->role === 'siswa';
        return Inertia::render('Ruang/Show', [
            'ruang' => $ruang->kepala(),
            'deskripsi_html' => $ruang->deskripsi ? teks($ruang->deskripsi) : null,
            'jadwal' => $ruang->jadwal->map(fn ($j) => [
                'id' => $j->id,
                'waktu' => Jadwal::HARI[$j->hari] . ', ' . substr($j->jam_mulai, 0, 5) . '–' . substr($j->jam_selesai, 0, 5),
                'link_meet' => $j->link_meet,
                'link_pendek' => $j->link_meet ? \Illuminate\Support\Str::limit($j->link_meet, 40) : null,
            ]),
            'pertemuan' => $pertemuan->map(function ($p) use ($siswa, $presensiSaya, $selesaiSaya, $jumlahSiswa) {
                $presensi = null;
                if ($p->presensi_buka) {
                    if ($siswa) {
                        $st = $presensiSaya[$p->id] ?? null;
                        $presensi = match (true) {
                            (bool) $st => [$st === 'hadir' ? 'hijau' : ($st === 'alpa' ? 'merah' : 'biru'), Presensi::STATUS[$st], false],
                            $p->presensiTerbuka() => ['hijau', 'Presensi dibuka s.d. ' . $p->presensi_tutup->format('H.i'), false],
                            now()->lt($p->presensi_buka) => ['', 'Presensi ' . $p->presensi_buka->format('H.i'), false],
                            default => ['merah', 'Tidak presensi', false],
                        };
                    } else {
                        $presensi = [$p->presensiTerbuka() ? 'hijau' : '', 'Hadir ' . $p->hadir_count . '/' . $jumlahSiswa, true];
                    }
                }
                return [
                    'id' => $p->id, 'urutan' => $p->urutan, 'judul' => $p->judul, 'tanggal' => tgl_panjang($p->tanggal),
                    'terbit' => (bool) $p->terbit, 'video' => (bool) $p->video_url, 'lampiran' => (bool) $p->lampiran_path,
                    'presensi' => $presensi ? ['kelas' => $presensi[0], 'label' => $presensi[1], 'ikon' => $presensi[2]] : null,
                    'beres' => $siswa && isset($selesaiSaya[$p->id]),
                ];
            }),
        ]);
    }

    public function anggota(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas.wali', 'guru');
        $siswa = $ruang->siswa()->get();
        $wali = $ruang->kelas->wali;
        return Inertia::render('Ruang/Anggota', [
            'ruang' => $ruang->kepala(),
            'siswa' => $siswa->map(fn ($s) => [
                'id' => $s->id, 'name' => $s->name, 'username' => $s->username, 'jk' => $s->jk,
                'terakhir_aktif' => $s->terakhir_aktif_at ? $s->terakhir_aktif_at->locale('id')->diffForHumans() : 'Belum pernah',
            ]),
            'pengajar' => [
                'guru' => ['nama' => $ruang->guru?->name ?? 'Belum ditentukan', 'inisial' => $ruang->guru?->inisial() ?? '?'],
                'wali' => $wali ? ['nama' => $wali->name, 'inisial' => $wali->inisial()] : null,
            ],
        ]);
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
        $baris = $siswa->values()->map(function ($s) use ($pertemuan, $data) {
            $sel = [];
            $h = $is = $a = 0;
            foreach ($pertemuan as $p) {
                $st = $data[$s->id][$p->id]->status ?? null;
                if (!$st && $p->presensi_tutup && $p->presensi_tutup->isPast()) $st = 'alpa';
                if ($st === 'hadir') $h++;
                elseif ($st === 'izin' || $st === 'sakit') $is++;
                elseif ($st === 'alpa') $a++;
                $sel[] = $st;
            }
            $wajib = $h + $a;
            return ['id' => $s->id, 'nama' => $s->name, 'sel' => $sel, 'h' => $h, 'is' => $is, 'a' => $a, 'persen' => $wajib ? round($h / $wajib * 100) : null];
        });
        return Inertia::render('Ruang/Presensi', [
            'ruang' => $ruang->kepala(),
            'status' => Presensi::STATUS,
            'pertemuan' => $pertemuan->map(fn ($p) => ['id' => $p->id, 'urutan' => $p->urutan, 'judul' => $p->judul, 'tanggal' => $p->tanggal->format('d/m')]),
            'baris' => $baris,
        ]);
    }

    public function nilai(Request $r, Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $rapor = Rapor::ruang($ruang);
        if ($r->user()->role === 'siswa') {
            $rapor['baris'] = array_values(array_filter($rapor['baris'], fn ($b) => $b['siswa']->id === $r->user()->id));
        }
        $baris = array_map(fn ($b) => [
            'id' => $b['siswa']->id, 'nama' => $b['siswa']->name,
            'tugas' => array_values($b['tugas']), 'kuis' => array_values($b['kuis']),
            'rata_tugas' => $b['rata_tugas'], 'rata_kuis' => $b['rata_kuis'], 'persen_hadir' => $b['persen_hadir'],
            'hadir' => $b['hadir'], 'alpa' => $b['alpa'], 'akhir' => $b['akhir'], 'predikat' => predikat($b['akhir']),
        ], $rapor['baris']);
        return Inertia::render('Ruang/Nilai', [
            'ruang' => $ruang->kepala(),
            'bobot' => $ruang->only('bobot_tugas', 'bobot_kuis', 'bobot_kehadiran', 'kkm'),
            'tugas' => $rapor['tugas']->map(fn ($t) => ['id' => $t->id, 'judul' => $t->judul, 'tenggat' => tgl($t->tenggat)])->values(),
            'kuis' => $rapor['kuis']->map(fn ($k) => ['id' => $k->id, 'judul' => $k->judul, 'tanggal' => tgl($k->mulai), 'tampilkan_nilai' => (bool) $k->tampilkan_nilai])->values(),
            'baris' => $baris,
        ]);
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
        return Inertia::render('Ruang/Pengaturan', [
            'ruang' => $ruang->kepala(),
            'pengaturan' => $ruang->only('deskripsi', 'kkm', 'bobot_tugas', 'bobot_kuis', 'bobot_kehadiran'),
            'jadwal' => $ruang->jadwal->map(fn ($j) => [
                'id' => $j->id, 'link_meet' => $j->link_meet,
                'waktu' => Jadwal::HARI[$j->hari] . ', ' . substr($j->jam_mulai, 0, 5) . '–' . substr($j->jam_selesai, 0, 5),
            ]),
        ]);
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
