<?php

namespace App\Modules\Ruang\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kelas\Models\Kelas;
use App\Modules\Mapel\Models\Mapel;
use App\Modules\Pengguna\Models\User;
use App\Modules\Ruang\Models\Jadwal;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Sekolah\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/** Pembagian mengajar: mapel apa diajar siapa di kelas mana, plus jadwal & link kelas daring */
class PembagianController extends Controller
{
    public function index(Request $r)
    {
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $kelas = $kelasList->firstWhere('id', (int) $r->query('kelas')) ?? $kelasList->first();
        $ta = TahunAjaran::aktif();
        $ruang = $kelas ? Ruang::with('mapel', 'guru', 'jadwal')->where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta?->id)->get()->sortBy('mapel.nama')->values() : collect();
        $mapel = Mapel::orderBy('nama')->get();
        $guru = User::where('role', 'guru')->where('aktif', true)->orderBy('name')->get();
        return Inertia::render('Ruang/Pembagian', [
            'kelasList' => $kelasList->map(fn ($k) => $k->only('id', 'nama')),
            'kelas' => $kelas?->only('id', 'nama'),
            'tahunAjaran' => $ta?->label(),
            'hari' => Jadwal::HARI,
            'ruang' => $ruang->map(fn ($x) => [
                'id' => $x->id, 'mapel_id' => $x->mapel_id, 'mapel' => $x->mapel->nama, 'guru_id' => $x->guru_id,
                'jadwal' => $x->jadwal->map(fn ($j) => [
                    'id' => $j->id, 'link_meet' => (bool) $j->link_meet,
                    'waktu' => Jadwal::HARI[$j->hari] . ' ' . substr($j->jam_mulai, 0, 5) . '–' . substr($j->jam_selesai, 0, 5),
                ]),
            ]),
            'mapelTersedia' => $mapel->whereNotIn('id', $ruang->pluck('mapel_id'))->map(fn ($m) => $m->only('id', 'nama'))->values(),
            'guru' => $guru->map(fn ($g) => $g->only('id', 'name')),
        ]);
    }

    public function simpan(Request $r)
    {
        $ta = TahunAjaran::aktifId();
        abort_unless($ta, 422, 'Belum ada tahun ajaran aktif.');
        $data = $r->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'guru')],
        ]);
        $ruang = Ruang::firstOrNew(['tahun_ajaran_id' => $ta, 'kelas_id' => $data['kelas_id'], 'mapel_id' => $data['mapel_id']]);
        if (!$ruang->exists) $ruang->kkm = (int) setting('kkm');
        $ruang->guru_id = $data['guru_id'] ?? null;
        $ruang->save();
        return redirect()->route('admin.ruang', ['kelas' => $data['kelas_id']])->with('ok', 'Pembagian mengajar disimpan.');
    }

    public function hapus(Ruang $ruang)
    {
        if ($ruang->pertemuan()->exists() || $ruang->tugas()->exists()) {
            return back()->with('error', 'Ruang belajar sudah berisi materi/tugas. Ganti gurunya saja, jangan dihapus.');
        }
        $ruang->delete();
        return back()->with('ok', 'Ruang belajar dihapus.');
    }

    public function tambahJadwal(Request $r, Ruang $ruang)
    {
        $data = $r->validate([
            'hari' => 'required|integer|between:1,6',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'link_meet' => 'nullable|url|max:255',
        ], ['jam_selesai.after' => 'Jam selesai harus setelah jam mulai.']);
        // Cegah bentrok jadwal di kelas yang sama
        $bentrok = Jadwal::whereHas('ruang', fn ($q) => $q->where('kelas_id', $ruang->kelas_id)->where('tahun_ajaran_id', $ruang->tahun_ajaran_id))
            ->where('hari', $data['hari'])->where('jam_mulai', '<', $data['jam_selesai'])->where('jam_selesai', '>', $data['jam_mulai'])
            ->with('ruang.mapel')->first();
        if ($bentrok) return back()->with('error', 'Bentrok dengan ' . $bentrok->ruang->mapel->nama . ' (' . substr($bentrok->jam_mulai, 0, 5) . '–' . substr($bentrok->jam_selesai, 0, 5) . ').');
        $ruang->jadwal()->create($data);
        return back()->with('ok', 'Jadwal ditambahkan.');
    }

    public function hapusJadwal(Jadwal $jadwal)
    {
        $jadwal->delete();
        return back()->with('ok', 'Jadwal dihapus.');
    }
}
