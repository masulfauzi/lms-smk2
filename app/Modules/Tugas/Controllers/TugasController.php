<?php

namespace App\Modules\Tugas\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Tugas\Models\Pengumpulan;
use App\Modules\Pertemuan\Models\Pertemuan;
use App\Modules\Ruang\Models\Ruang;
use App\Modules\Tugas\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        return view('tugas::index', compact('ruang', 'tugas', 'milikSaya', 'jumlahSiswa'));
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
        $pertemuan = Pertemuan::where('ruang_id', $ruang->id)->orderBy('urutan')->get(['id', 'urutan', 'judul']);
        return view('tugas::form', compact('ruang', 'tugas', 'pertemuan'));
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
        $ruang->load('mapel', 'kelas');
        $pertemuan = Pertemuan::where('ruang_id', $ruang->id)->orderBy('urutan')->get(['id', 'urutan', 'judul']);
        return view('tugas::form', compact('ruang', 'tugas', 'pertemuan'));
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
            return view('tugas::siswa', compact('ruang', 'tugas', 'milikSaya'));
        }
        $siswa = $ruang->siswa()->get();
        $kumpul = Pengumpulan::where('tugas_id', $tugas->id)->get()->keyBy('siswa_id');
        $pilih = $kumpul->get((int) $r->query('siswa')) ?? $kumpul->sortBy('dikumpulkan_at')->firstWhere('nilai', null);
        return view('tugas::guru', compact('ruang', 'tugas', 'siswa', 'kumpul', 'pilih'));
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
