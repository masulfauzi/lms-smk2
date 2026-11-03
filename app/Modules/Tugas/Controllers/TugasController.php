<?php

namespace App\Modules\Tugas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Tugas\Models\Pengumpulan;
use App\Modules\Tugas\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TugasController extends Controller
{
    private const BERKAS = 'file|max:20480|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt';

    public function index(Request $r, Ruang $ruang)
    {
        $u = $r->user();
        $ruang->load('mapel', 'kelas');
        $tugas = Tugas::where('ruang_id', $ruang->id)->withCount([
            'pengumpulan',
            'pengumpulan as belum_dinilai_count' => fn ($q) => $q->whereNull('nilai'),
        ])->orderByDesc('tenggat')->get();
        $milikSaya = $u->role === 'siswa'
            ? Pengumpulan::where('siswa_id', $u->id)->whereIn('tugas_id', $tugas->pluck('id'))->get()->keyBy('tugas_id')
            : collect();
        $jumlahSiswa = $ruang->siswa()->count();
        $siswa = $u->role === 'siswa';
        return Inertia::render('Tugas/Index', [
            'ruang' => $ruang->kepala(),
            'tugas' => $tugas->map(function ($t) use ($siswa, $milikSaya, $jumlahSiswa) {
                $lewat = $t->lewatTenggat();
                $lencana = [];
                if ($siswa) {
                    $k = $milikSaya[$t->id] ?? null;
                    $lencana[] = match (true) {
                        $k && $k->nilai !== null => ['hijau', 'Nilai ' . $k->nilai],
                        (bool) $k => ['biru', $k->terlambat ? 'Terkumpul (terlambat)' : 'Terkumpul'],
                        $lewat => ['merah', $t->boleh_terlambat ? 'Terlambat' : 'Tidak mengumpulkan'],
                        default => ['oranye', sisa_waktu($t->tenggat)],
                    };
                } else {
                    $lencana[] = ['biru', $t->pengumpulan_count . '/' . $jumlahSiswa . ' terkumpul'];
                    if ($t->belum_dinilai_count) $lencana[] = ['kuning', $t->belum_dinilai_count . ' perlu dinilai'];
                }
                return [
                    'id' => $t->id, 'judul' => $t->judul, 'lewat' => $lewat,
                    'keterangan' => 'Tenggat ' . tgl($t->tenggat, true) . ' · ' . Tugas::JENIS[$t->jenis_kumpul] . ($t->boleh_terlambat ? '' : ' · tidak menerima terlambat'),
                    'lencana' => $lencana,
                ];
            }),
        ]);
    }

    public function create(Request $r, Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $tugas = new Tugas([
            'tenggat' => today()->addDays(7)->setTime(23, 59),
            'jenis_kumpul' => 'keduanya',
            'boleh_terlambat' => true,
            'pertemuan_id' => $r->query('pertemuan'),
        ]);
        return $this->form($ruang, $tugas);
    }

    /** Halaman form tambah/ubah tugas */
    private function form(Ruang $ruang, Tugas $t)
    {
        $pertemuan = Pertemuan::where('ruang_id', $ruang->id)->orderBy('urutan')->get(['id', 'urutan', 'judul']);
        return Inertia::render('Tugas/Form', [
            'ruang' => $ruang->kepala(),
            'tugas' => [
                'id' => $t->exists ? $t->id : null,
                'judul' => $t->judul ?? '',
                'instruksi' => $t->instruksi ?? '',
                'lampiran_nama' => $t->lampiran_nama,
                'tenggat' => $t->tenggat?->format('Y-m-d\TH:i'),
                'jenis_kumpul' => $t->jenis_kumpul,
                'pertemuan_id' => $t->pertemuan_id ? (int) $t->pertemuan_id : '',
                'boleh_terlambat' => (bool) $t->boleh_terlambat,
            ],
            'jenis' => Tugas::JENIS,
            'pertemuan' => $pertemuan->map(fn ($p) => ['id' => $p->id, 'label' => 'Pertemuan ' . $p->urutan . ': ' . Str::limit($p->judul, 40)]),
        ]);
    }

    public function store(Request $r, Ruang $ruang)
    {
        $t = new Tugas($this->validasi($r, $ruang) + ['ruang_id' => $ruang->id]);
        $this->lampiran($r, $t, $ruang);
        $t->save();
        return redirect()->route('tugas.show', [$ruang, $t])->with('ok', 'Tugas diterbitkan.');
    }

    public function edit(Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        return $this->form($ruang, $tugas);
    }

    public function update(Request $r, Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        $tugas->fill($this->validasi($r, $ruang));
        $this->lampiran($r, $tugas, $ruang);
        $tugas->save();
        // Status terlambat mengikuti tenggat terbaru
        foreach ($tugas->pengumpulan as $p) {
            $p->update(['terlambat' => $p->dikumpulkan_at->gt($tugas->tenggat)]);
        }
        return redirect()->route('tugas.show', [$ruang, $tugas])->with('ok', 'Tugas diperbarui.');
    }

    public function destroy(Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        foreach ($tugas->pengumpulan as $p) if ($p->file_path) Storage::delete($p->file_path);
        if ($tugas->lampiran_path) Storage::delete($tugas->lampiran_path);
        $tugas->delete();
        return redirect()->route('tugas.index', $ruang)->with('ok', 'Tugas dihapus.');
    }

    public function show(Request $r, Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        $u = $r->user();
        $ruang->load('mapel', 'kelas');
        if ($u->role === 'siswa') {
            $milikSaya = Pengumpulan::where('tugas_id', $tugas->id)->where('siswa_id', $u->id)->first();
            $m = $milikSaya;
            return Inertia::render('Tugas/Siswa', [
                'ruang' => $ruang->kepala(),
                'tugas' => ['id' => $tugas->id],
                'soal' => $this->soal($ruang, $tugas),
                'jenisKumpul' => $tugas->jenis_kumpul,
                'bisaDikumpulkan' => $tugas->bisaDikumpulkan(),
                'lewatTenggat' => $tugas->lewatTenggat(),
                'milikSaya' => $m ? [
                    'id' => $m->id, 'nilai' => $m->nilai, 'tuntas' => $m->nilai !== null && $m->nilai >= $ruang->kkm,
                    'komentar_html' => $m->komentar ? teks($m->komentar) : null,
                    'dikumpulkan' => tgl($m->dikumpulkan_at, true), 'terlambat' => (bool) $m->terlambat,
                    'teks' => $m->teks, 'teks_html' => $m->teks ? teks($m->teks) : null,
                    'file_nama' => $m->file_path ? $m->file_nama : null,
                ] : null,
            ]);
        }
        $siswa = $ruang->siswa()->get();
        $kumpul = Pengumpulan::where('tugas_id', $tugas->id)->get()->keyBy('siswa_id');
        $pilih = $kumpul->get((int) $r->query('siswa')) ?? $kumpul->sortBy('dikumpulkan_at')->firstWhere('nilai', null);
        $lewat = $tugas->lewatTenggat();
        $pilih?->load('siswa');
        return Inertia::render('Tugas/Guru', [
            'ruang' => $ruang->kepala(),
            'tugas' => ['id' => $tugas->id],
            'soal' => $this->soal($ruang, $tugas),
            'jumlahKumpul' => $kumpul->count(),
            'pilih' => $pilih ? [
                'id' => $pilih->id, 'siswa_id' => $pilih->siswa_id, 'nama' => $pilih->siswa->name,
                'dikumpulkan' => tgl($pilih->dikumpulkan_at, true), 'terlambat' => (bool) $pilih->terlambat,
                'teks_html' => $pilih->teks ? teks($pilih->teks) : null,
                'file_nama' => $pilih->file_path ? $pilih->file_nama : null,
                'nilai' => $pilih->nilai, 'komentar' => $pilih->komentar ?? '',
            ] : null,
            'siswa' => $siswa->values()->map(function ($s) use ($kumpul, $lewat) {
                $k = $kumpul[$s->id] ?? null;
                return [
                    'id' => $s->id, 'nama' => $s->name, 'kumpul' => (bool) $k,
                    'waktu' => $k ? tgl($k->dikumpulkan_at, true) : 'Belum mengumpulkan',
                    'lencana' => match (true) {
                        $k && $k->nilai !== null => ['hijau', (string) $k->nilai],
                        (bool) $k => [$k->terlambat ? 'oranye' : 'kuning', $k->terlambat ? 'Terlambat' : 'Belum dinilai'],
                        $lewat => ['merah', 'Tidak ada'],
                        default => ['', '-'],
                    },
                ];
            }),
        ]);
    }

    /** Props bagian soal tugas (komponen LampiranSoal.vue, pengganti tugas::_soal) */
    private function soal(Ruang $ruang, Tugas $t): array
    {
        return [
            'judul' => $t->judul,
            'tenggat' => tgl_panjang($t->tenggat) . ' pukul ' . $t->tenggat->format('H.i'),
            'instruksi_html' => $t->instruksi ? teks($t->instruksi) : null,
            'lampiran_nama' => $t->lampiran_path ? $t->lampiran_nama : null,
            'url_lampiran' => $t->lampiran_path ? route('berkas.tugas', [$ruang, $t]) : null,
        ];
    }

    public function kumpul(Request $r, Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        $u = $r->user();
        if (!$tugas->bisaDikumpulkan()) return back()->with('error', 'Tenggat sudah lewat dan guru tidak menerima pengumpulan terlambat.');
        $lama = Pengumpulan::where('tugas_id', $tugas->id)->where('siswa_id', $u->id)->first();
        if ($lama?->nilai !== null) return back()->with('error', 'Tugas sudah dinilai, tidak bisa dikumpulkan ulang.');

        $aturan = ['teks' => 'nullable|string|max:20000', 'berkas' => 'nullable|' . self::BERKAS];
        $r->validate($aturan, ['berkas.max' => 'Ukuran file maksimal 20 MB.', 'berkas.mimes' => 'Format file tidak didukung.', 'berkas.extensions' => 'Format file tidak didukung.']);
        $adaTeks = filled($r->teks) && $tugas->jenis_kumpul !== 'file';
        $adaFile = $r->hasFile('berkas') && $tugas->jenis_kumpul !== 'teks';
        if (!$adaTeks && !$adaFile && !($lama?->file_path && $tugas->jenis_kumpul !== 'teks')) {
            return back()->withInput()->with('error', $tugas->jenis_kumpul === 'file' ? 'Lampirkan file jawaban.' : 'Isi jawaban atau lampirkan file.');
        }

        $p = $lama ?? new Pengumpulan(['tugas_id' => $tugas->id, 'siswa_id' => $u->id]);
        $p->teks = $tugas->jenis_kumpul === 'file' ? null : $r->teks;
        if ($adaFile) {
            if ($p->file_path) Storage::delete($p->file_path);
            $f = $r->file('berkas');
            $p->file_path = $f->store("tugas/{$tugas->id}");
            $p->file_nama = $f->getClientOriginalName();
        }
        $p->dikumpulkan_at = now();
        $p->terlambat = $tugas->lewatTenggat();
        $p->save();
        return back()->with('ok', $p->terlambat ? 'Tugas terkumpul (terlambat).' : 'Tugas berhasil dikumpulkan.');
    }

    public function nilai(Request $r, Ruang $ruang, Tugas $tugas, Pengumpulan $pengumpulan)
    {
        $this->milik($tugas, $ruang);
        abort_unless($pengumpulan->tugas_id === $tugas->id, 404);
        $data = $r->validate(['nilai' => 'required|integer|min:0|max:100', 'komentar' => 'nullable|string|max:2000']);
        $pengumpulan->update($data + ['dinilai_at' => now()]);

        // Lanjut ke pengumpulan berikutnya yang belum dinilai
        $berikut = Pengumpulan::where('tugas_id', $tugas->id)->whereNull('nilai')->oldest('dikumpulkan_at')->first();
        return redirect()->route('tugas.show', [$ruang, $tugas] + ($berikut ? ['siswa' => $berikut->siswa_id] : []))
            ->with('ok', 'Nilai ' . $pengumpulan->siswa->name . ' disimpan.' . ($berikut ? '' : ' Semua pengumpulan sudah dinilai.'));
    }

    private function validasi(Request $r, Ruang $ruang): array
    {
        $data = $r->validate([
            'judul' => 'required|string|max:160',
            'instruksi' => 'nullable|string|max:20000',
            'tenggat' => 'required|date',
            'jenis_kumpul' => 'required|in:keduanya,file,teks',
            'pertemuan_id' => 'nullable|integer',
            'lampiran' => 'nullable|' . self::BERKAS,
        ]);
        if (!empty($data['pertemuan_id']) && !Pertemuan::where('ruang_id', $ruang->id)->whereKey($data['pertemuan_id'])->exists()) {
            $data['pertemuan_id'] = null;
        }
        unset($data['lampiran']);
        return $data + ['boleh_terlambat' => $r->boolean('boleh_terlambat')];
    }

    private function lampiran(Request $r, Tugas $t, Ruang $ruang): void
    {
        if (!$r->hasFile('lampiran')) return;
        if ($t->lampiran_path) Storage::delete($t->lampiran_path);
        $f = $r->file('lampiran');
        $t->lampiran_path = $f->store("soal-tugas/ruang-{$ruang->id}");
        $t->lampiran_nama = $f->getClientOriginalName();
    }

    /** Unduh lampiran soal tugas */
    public function berkas(Ruang $ruang, Tugas $tugas)
    {
        $this->milik($tugas, $ruang);
        return $this->kirimBerkas($tugas->lampiran_path, $tugas->lampiran_nama);
    }

    /** Unduh file pengumpulan (siswa hanya miliknya sendiri) */
    public function berkasKumpulan(Request $r, Ruang $ruang, Pengumpulan $pengumpulan)
    {
        $pengumpulan->load('tugas');
        $this->milik($pengumpulan->tugas, $ruang);
        $u = $r->user();
        abort_if($u->role === 'siswa' && $pengumpulan->siswa_id !== $u->id, 403);
        return $this->kirimBerkas($pengumpulan->file_path, $pengumpulan->file_nama);
    }
}
