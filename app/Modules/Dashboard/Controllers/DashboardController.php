<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kelas\Models\Kelas;
use App\Modules\Kuis\Models\Kuis;
use App\Modules\Pengguna\Models\User;
use App\Modules\Pengumuman\Models\Pengumuman;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Pertemuan\Models\Presensi;
use App\Modules\Ruang\Models\Jadwal;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Sekolah\Models\TahunAjaran;
use App\Modules\Tugas\Models\Pengumpulan;
use App\Modules\Tugas\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $pengumuman = Pengumuman::untuk($u)->limit(4)->get();
        return match ($u->role) {
            'admin' => $this->admin($pengumuman),
            'guru' => $this->guru($u, $pengumuman),
            default => $this->siswa($u, $pengumuman),
        };
    }

    private function jadwalHariIni($ruangIds)
    {
        return Jadwal::with('ruang.mapel', 'ruang.kelas', 'ruang.guru')
            ->whereIn('ruang_id', $ruangIds)->where('hari', now()->dayOfWeekIso)
            ->orderBy('jam_mulai')->get();
    }

    /** Props kartu "Jadwal hari ini" (komponen JadwalHariIni.vue) */
    private function kartuJadwal($jadwal, User $u): array
    {
        $jam = now()->format('H:i:s');
        return [
            'tanggal' => tgl_panjang(now()),
            'daftar' => $jadwal->map(fn ($j) => [
                'id' => $j->id,
                'mapel' => $j->ruang->mapel->nama,
                'warna' => $j->ruang->warna(),
                'jam' => substr($j->jam_mulai, 0, 5) . '–' . substr($j->jam_selesai, 0, 5),
                'keterangan' => $u->role === 'siswa' ? ($j->ruang->guru?->name ?? '-') : $j->ruang->kelas->nama,
                'berlangsung' => $jam >= $j->jam_mulai && $jam <= $j->jam_selesai,
                'link_meet' => $j->link_meet,
                'url_ruang' => route('ruang.show', $j->ruang_id),
            ])->values(),
        ];
    }

    /** Props kartu "Pengumuman" (komponen DaftarPengumuman.vue) */
    private function kartuPengumuman($pengumuman): array
    {
        return $pengumuman->map(fn ($p) => [
            'id' => $p->id,
            'judul' => $p->judul,
            'disematkan' => (bool) $p->disematkan,
            'penulis' => $p->user->name,
            'waktu' => $p->created_at->locale('id')->diffForHumans(),
            'ringkas' => Str::limit($p->isi, 140),
        ])->values()->all();
    }

    private function namaDepan(User $u): string
    {
        return Str::before($u->name, ' ');
    }

    private function admin($pengumuman)
    {
        $ta = TahunAjaran::aktifId();
        $siswaAktif = User::where('role', 'siswa')->where('aktif', true);
        $stat = [
            'siswa' => (clone $siswaAktif)->count(),
            'guru' => User::where('role', 'guru')->where('aktif', true)->count(),
            'kelas' => Kelas::count(),
            'ruang' => Ruang::where('tahun_ajaran_id', $ta)->count(),
            'aktif_hari_ini' => (clone $siswaAktif)->where('terakhir_aktif_at', '>=', today())->count(),
        ];

        // Kehadiran PJJ hari ini per kelas: presensi hadir dibanding pertemuan yang dijadwalkan hari ini
        $hariIni = Pertemuan::with('ruang.kelas')->whereDate('tanggal', today())
            ->whereHas('ruang', fn ($q) => $q->where('tahun_ajaran_id', $ta))->get();
        $perKelas = [];
        foreach ($hariIni->groupBy(fn ($p) => $p->ruang->kelas_id) as $kelasId => $daftar) {
            $kelas = $daftar->first()->ruang->kelas;
            $jumlahSiswa = User::where('role', 'siswa')->where('kelas_id', $kelasId)->where('aktif', true)->count();
            $hadir = Presensi::whereIn('pertemuan_id', $daftar->pluck('id'))->where('status', 'hadir')->count();
            $target = $jumlahSiswa * $daftar->count();
            $perKelas[] = ['kelas' => $kelas->nama, 'pertemuan' => $daftar->count(), 'persen' => $target ? round($hadir / $target * 100) : 0];
        }
        usort($perKelas, fn ($a, $b) => strnatcmp($a['kelas'], $b['kelas']));

        $guruBelumIsi = Ruang::with('guru', 'mapel', 'kelas')->where('tahun_ajaran_id', $ta)
            ->whereDoesntHave('pertemuan', fn ($q) => $q->where('tanggal', '>=', now()->subDays(7)))
            ->limit(8)->get();

        return Inertia::render('Dashboard/Admin', [
            'nama' => $this->namaDepan(request()->user()),
            'stat' => $stat,
            'perKelas' => $perKelas,
            'guruBelumIsi' => $guruBelumIsi->map(fn ($r) => [
                'id' => $r->id,
                'judul' => $r->mapel->nama . ' · ' . $r->kelas->nama,
                'guru' => $r->guru?->name ?? 'Belum ada guru',
            ]),
            'pengumuman' => $this->kartuPengumuman($pengumuman),
        ]);
    }

    private function guru(User $u, $pengumuman)
    {
        $ruang = $u->ruangQuery()->with('mapel', 'kelas')->withCount('pertemuan')->get()
            ->sortBy(fn ($r) => $r->kelas->tingkat . $r->kelas->nama . $r->mapel->nama)->values();
        $ids = $ruang->pluck('id');
        $perluDinilai = Pengumpulan::with('siswa', 'tugas.ruang.mapel', 'tugas.ruang.kelas')
            ->whereNull('nilai')->whereHas('tugas', fn ($q) => $q->whereIn('ruang_id', $ids))
            ->oldest('dikumpulkan_at')->limit(8)->get();
        $jumlahPerluDinilai = Pengumpulan::whereNull('nilai')->whereHas('tugas', fn ($q) => $q->whereIn('ruang_id', $ids))->count();
        $kuisAktif = Kuis::with('ruang.mapel', 'ruang.kelas')->withCount(['percobaan' => fn ($q) => $q->whereNotNull('selesai_at')])
            ->whereIn('ruang_id', $ids)->where('terbit', true)->where('selesai', '>=', now())->orderBy('mulai')->limit(5)->get();
        $jadwal = $this->jadwalHariIni($ids);
        return Inertia::render('Dashboard/Guru', [
            'nama' => $this->namaDepan($u),
            'tanggal' => tgl_panjang(now()),
            'ruang' => $ruang->map(fn ($r) => [
                'id' => $r->id, 'mapel' => $r->mapel->nama, 'warna' => $r->warna(),
                'keterangan' => $r->kelas->nama . ' · ' . $r->pertemuan_count . ' pertemuan',
            ]),
            'perluDinilai' => $perluDinilai->map(fn ($p) => [
                'id' => $p->id,
                'judul' => $p->siswa->name . ' · ' . $p->tugas->judul,
                'keterangan' => $p->tugas->ruang->mapel->nama . ' ' . $p->tugas->ruang->kelas->nama . ' · dikumpulkan ' . $p->dikumpulkan_at->locale('id')->diffForHumans(),
                'terlambat' => (bool) $p->terlambat,
                'url' => route('tugas.show', [$p->tugas->ruang_id, $p->tugas_id]) . '?siswa=' . $p->siswa_id,
            ]),
            'jumlahPerluDinilai' => $jumlahPerluDinilai,
            'kuisAktif' => $kuisAktif->map(fn ($k) => [
                'id' => $k->id, 'judul' => $k->judul,
                'keterangan' => $k->ruang->mapel->nama . ' ' . $k->ruang->kelas->nama . ' · ' . $k->percobaan_count . ' selesai · tutup ' . tgl($k->selesai, true),
                'url' => route('kuis.show', [$k->ruang_id, $k]),
            ]),
            'jadwal' => $this->kartuJadwal($jadwal, $u),
            'pengumuman' => $this->kartuPengumuman($pengumuman),
        ]);
    }

    private function siswa(User $u, $pengumuman)
    {
        $ruang = $u->ruangQuery()->with('mapel', 'guru')->get()->sortBy(fn ($r) => $r->mapel->nama)->values();
        $ids = $ruang->pluck('id');

        $tugas = Tugas::with('ruang.mapel')->whereIn('ruang_id', $ids)
            ->whereDoesntHave('pengumpulan', fn ($q) => $q->where('siswa_id', $u->id))
            ->where(fn ($q) => $q->where('tenggat', '>=', now())->orWhere('boleh_terlambat', true))
            ->where('tenggat', '>=', now()->subDays(14))
            ->orderBy('tenggat')->limit(6)->get();
        $kuis = Kuis::with('ruang.mapel')->whereIn('ruang_id', $ids)->where('terbit', true)
            ->where('selesai', '>=', now())->where('mulai', '<=', now()->addDays(7))
            ->whereDoesntHave('percobaan', fn ($q) => $q->where('siswa_id', $u->id)->whereNotNull('selesai_at'))
            ->orderBy('mulai')->get();
        $presensiTerbuka = Pertemuan::with('ruang.mapel')->whereIn('ruang_id', $ids)->where('terbit', true)
            ->where('presensi_buka', '<=', now())->where('presensi_tutup', '>=', now())
            ->whereDoesntHave('presensi', fn ($q) => $q->where('siswa_id', $u->id))->get();

        // Progres materi per mapel
        $progres = [];
        foreach ($ruang as $r) {
            $total = Pertemuan::where('ruang_id', $r->id)->where('terbit', true)->where('tanggal', '<=', today())->count();
            $selesai = \DB::table('materi_selesai')->join('pertemuan', 'pertemuan.id', '=', 'materi_selesai.pertemuan_id')
                ->where('pertemuan.ruang_id', $r->id)->where('materi_selesai.siswa_id', $u->id)->count();
            $progres[$r->id] = $total ? min(100, round($selesai / $total * 100)) : 0;
        }

        $jadwal = $this->jadwalHariIni($ids);
        return Inertia::render('Dashboard/Siswa', [
            'nama' => $this->namaDepan($u),
            'tanggal' => tgl_panjang(now()),
            'kelas' => $u->kelas?->nama ?? '-',
            'presensiTerbuka' => $presensiTerbuka->map(fn ($p) => [
                'id' => $p->id,
                'mapel' => $p->ruang->mapel->nama,
                'keterangan' => 'Pertemuan ' . $p->urutan . ': ' . $p->judul . ' · tutup pukul ' . $p->presensi_tutup->format('H.i'),
                'url_hadir' => route('pertemuan.hadir', [$p->ruang_id, $p]),
            ]),
            'tugas' => $tugas->map(function ($t) {
                $lewat = $t->lewatTenggat();
                return [
                    'id' => $t->id, 'judul' => $t->judul, 'lewat' => $lewat,
                    'keterangan' => $t->ruang->mapel->nama . ' · tenggat ' . tgl($t->tenggat, true),
                    'lencana' => $lewat ? 'merah' : ($t->tenggat->diffInHours(now(), true) < 24 ? 'oranye' : 'biru'),
                    'label' => $lewat ? 'Terlambat' : sisa_waktu($t->tenggat),
                    'url' => route('tugas.show', [$t->ruang_id, $t]),
                ];
            }),
            'kuis' => $kuis->map(function ($k) {
                $st = $k->status();
                return [
                    'id' => $k->id, 'judul' => $k->judul, 'berlangsung' => $st === 'berlangsung',
                    'keterangan' => $k->ruang->mapel->nama . ' · ' . ($st === 'berlangsung' ? 'tutup ' . tgl($k->selesai, true) : 'dibuka ' . tgl($k->mulai, true)) . ' · ' . $k->durasi_menit . ' menit',
                    'url' => route('kuis.show', [$k->ruang_id, $k]),
                ];
            }),
            'ruang' => $ruang->map(fn ($r) => [
                'id' => $r->id, 'mapel' => $r->mapel->nama, 'warna' => $r->warna(),
                'guru' => $r->guru?->name ?? '-', 'progres' => $progres[$r->id],
            ]),
            'jadwal' => $this->kartuJadwal($jadwal, $u),
            'pengumuman' => $this->kartuPengumuman($pengumuman),
        ]);
    }

    public function jadwal(Request $r)
    {
        $u = $r->user();
        $q = Jadwal::with('ruang.mapel', 'ruang.kelas', 'ruang.guru')->orderBy('jam_mulai');
        $kelasList = collect();
        $kelasId = null;
        if ($u->role === 'admin') {
            $kelasList = Kelas::orderBy('tingkat')->orderBy('nama')->get();
            $kelasId = (int) $r->query('kelas', $kelasList->first()?->id);
            $q->whereHas('ruang', fn ($x) => $x->where('kelas_id', $kelasId)->where('tahun_ajaran_id', TahunAjaran::aktifId()));
        } else {
            $q->whereIn('ruang_id', $u->ruangQuery()->pluck('id'));
        }
        $perHari = $q->get()->groupBy('hari');
        $hariIni = now()->dayOfWeekIso;
        return Inertia::render('Dashboard/Jadwal', [
            'kelasList' => $kelasList->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama]),
            'kelasId' => $kelasId,
            'hari' => collect(Jadwal::HARI)->map(fn ($nama, $no) => [
                'no' => $no,
                'nama' => $nama,
                'hari_ini' => $no === $hariIni,
                'slot' => collect($perHari[$no] ?? [])->map(fn ($j) => [
                    'id' => $j->id,
                    'mapel' => $j->ruang->mapel->nama,
                    'warna' => $j->ruang->warna(),
                    'jam' => substr($j->jam_mulai, 0, 5) . '–' . substr($j->jam_selesai, 0, 5),
                    'keterangan' => $u->role === 'siswa' ? ($j->ruang->guru?->name ?? '-') : $j->ruang->kelas->nama,
                    'link_meet' => $j->link_meet,
                ])->values(),
            ])->values(),
        ]);
    }
}
