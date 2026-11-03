<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Data master: jurusan, mata pelajaran, kelas (satu form simpan untuk tambah & ubah) */
class MasterController extends Controller
{
    public function jurusan(Request $r)
    {
        $daftar = Jurusan::withCount('kelas')->orderBy('kode')->get();
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('admin.jurusan', compact('daftar', 'ubah'));
    }

    public function simpanJurusan(Request $r)
    {
        $id = $r->integer('id') ?: null;
        $data = $r->validate([
            'kode' => ['required', 'string', 'max:15', Rule::unique('jurusan')->ignore($id)],
            'nama' => 'required|string|max:120',
        ]);
        Jurusan::updateOrCreate(['id' => $id], $data);
        return redirect()->route('admin.jurusan')->with('ok', 'Jurusan disimpan.');
    }

    public function hapusJurusan(Jurusan $jurusan)
    {
        if ($jurusan->kelas()->exists()) return back()->with('error', 'Jurusan masih dipakai kelas.');
        $jurusan->delete();
        return back()->with('ok', 'Jurusan dihapus.');
    }

    public function mapel(Request $r)
    {
        $daftar = Mapel::orderBy('kelompok')->orderBy('nama')->get();
        $dipakai = \App\Models\Ruang::selectRaw('mapel_id, count(*) n')->groupBy('mapel_id')->pluck('n', 'mapel_id');
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('admin.mapel', compact('daftar', 'dipakai', 'ubah'));
    }

    public function simpanMapel(Request $r)
    {
        $id = $r->integer('id') ?: null;
        $data = $r->validate([
            'kode' => ['required', 'string', 'max:15', Rule::unique('mapel')->ignore($id)],
            'nama' => 'required|string|max:120',
            'kelompok' => 'required|in:Umum,Kejuruan,Muatan Lokal',
        ]);
        Mapel::updateOrCreate(['id' => $id], $data);
        return redirect()->route('admin.mapel')->with('ok', 'Mata pelajaran disimpan.');
    }

    public function hapusMapel(Mapel $mapel)
    {
        if (\App\Models\Ruang::where('mapel_id', $mapel->id)->exists()) return back()->with('error', 'Mapel masih dipakai di ruang belajar.');
        $mapel->delete();
        return back()->with('ok', 'Mata pelajaran dihapus.');
    }

    public function kelas(Request $r)
    {
        $daftar = Kelas::with('jurusan', 'wali')->withCount(['siswa', 'ruang'])->orderBy('tingkat')->orderBy('nama')->get();
        $jurusan = Jurusan::orderBy('kode')->get();
        $guru = User::where('role', 'guru')->where('aktif', true)->orderBy('name')->get();
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('admin.kelas', compact('daftar', 'jurusan', 'guru', 'ubah'));
    }

    public function simpanKelas(Request $r)
    {
        $id = $r->integer('id') ?: null;
        $data = $r->validate([
            'tingkat' => ['required', 'integer', Rule::in(Kelas::tingkatJenjang())],
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'nama' => ['required', 'string', 'max:40', Rule::unique('kelas')->ignore($id)],
            'wali_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'guru')],
        ]);
        Kelas::updateOrCreate(['id' => $id], $data);
        return redirect()->route('admin.kelas')->with('ok', 'Kelas disimpan.');
    }

    public function hapusKelas(Kelas $kelas)
    {
        if ($kelas->siswa()->exists()) return back()->with('error', 'Pindahkan siswa kelas ini terlebih dahulu.');
        $kelas->delete();
        return back()->with('ok', 'Kelas dihapus.');
    }
}
