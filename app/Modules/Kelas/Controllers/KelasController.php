<?php

namespace App\Modules\Kelas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Jurusan\Models\Jurusan;
use App\Modules\Kelas\Models\Kelas;
use App\Modules\Pengguna\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Data master kelas & wali kelas (satu form simpan untuk tambah & ubah) */
class KelasController extends Controller
{
    public function index(Request $r)
    {
        $daftar = Kelas::with('jurusan', 'wali')->withCount(['siswa', 'ruang'])->orderBy('tingkat')->orderBy('nama')->get();
        $jurusan = Jurusan::orderBy('kode')->get();
        $guru = User::where('role', 'guru')->where('aktif', true)->orderBy('name')->get();
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('kelas::index', compact('daftar', 'jurusan', 'guru', 'ubah'));
    }

    public function simpan(Request $r)
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

    public function hapus(Kelas $kelas)
    {
        if ($kelas->siswa()->exists()) return back()->with('error', 'Pindahkan siswa kelas ini terlebih dahulu.');
        $kelas->delete();
        return back()->with('ok', 'Kelas dihapus.');
    }
}
