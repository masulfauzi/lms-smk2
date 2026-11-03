<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Pengumpulan;
use App\Models\Pengumuman;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Ruang;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;

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

        return view('dashboard.admin', compact('stat', 'perKelas', 'guruBelumIsi', 'pengumuman'));
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
        return view('dashboard.guru', compact('ruang', 'perluDinilai', 'jumlahPerluDinilai', 'kuisAktif', 'jadwal', 'pengumuman'));
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
        return view('dashboard.siswa', compact('ruang', 'tugas', 'kuis', 'presensiTerbuka', 'progres', 'jadwal', 'pengumuman'));
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
        return view('dashboard.jadwal', compact('perHari', 'kelasList', 'kelasId'));
    }
}
