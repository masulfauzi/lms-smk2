<?php

namespace App\Modules\Sekolah\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sekolah\Models\Pengaturan;
use App\Modules\Sekolah\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SekolahController extends Controller
{
    public function index()
    {
        $tahun = TahunAjaran::withCount('ruang')->orderByDesc('nama')->orderByDesc('semester')->get();
        return Inertia::render('Sekolah/Index', [
            'profil' => collect(['nama_sekolah', 'jenjang', 'npsn', 'kkm', 'alamat', 'kepala_sekolah'])->mapWithKeys(fn ($k) => [$k => setting($k)]),
            'tahun' => $tahun->map(fn ($t) => [
                'id' => $t->id, 'nama' => $t->nama, 'semester' => $t->semester, 'label' => $t->label(),
                'aktif' => (bool) $t->aktif, 'ruang_count' => $t->ruang_count,
            ]),
        ]);
    }

    public function simpan(Request $r)
    {
        $data = $r->validate([
            'nama_sekolah' => 'required|string|max:120',
            'jenjang' => 'required|in:SD,SMP,SMA,SMK',
            'npsn' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:120',
            'kkm' => 'required|integer|min:0|max:100',
        ]);
        Pengaturan::simpan($data);
        return back()->with('ok', 'Profil sekolah disimpan.');
    }

    public function tambahTahun(Request $r)
    {
        $data = $r->validate([
            'nama' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => 'required|in:Ganjil,Genap',
            'salin' => 'nullable|boolean',
        ], ['nama.regex' => 'Format tahun ajaran: 2026/2027']);
        if (TahunAjaran::where('nama', $data['nama'])->where('semester', $data['semester'])->exists()) {
            return back()->with('error', 'Tahun ajaran tersebut sudah ada.');
        }
        DB::transaction(function () use ($data, $r) {
            $lama = TahunAjaran::aktif();
            $baru = TahunAjaran::create(['nama' => $data['nama'], 'semester' => $data['semester'], 'aktif' => false]);
            // Salin pembagian mengajar dari semester aktif agar admin tidak mengulang dari nol
            if ($r->boolean('salin') && $lama) {
                foreach (\App\Modules\Ruang\Models\Ruang::where('tahun_ajaran_id', $lama->id)->with('jadwal')->get() as $ru) {
                    $salinan = $ru->replicate()->fill(['tahun_ajaran_id' => $baru->id]);
                    $salinan->save();
                    foreach ($ru->jadwal as $j) $salinan->jadwal()->create($j->only('hari', 'jam_mulai', 'jam_selesai', 'link_meet'));
                }
            }
        });
        return back()->with('ok', 'Tahun ajaran ditambahkan. Aktifkan bila semester sudah dimulai.');
    }

    public function aktifkanTahun(TahunAjaran $tahun)
    {
        DB::transaction(function () use ($tahun) {
            TahunAjaran::query()->update(['aktif' => false]);
            $tahun->update(['aktif' => true]);
        });
        return back()->with('ok', 'Tahun ajaran aktif: ' . $tahun->label() . '.');
    }
}
