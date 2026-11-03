<?php

namespace App\Modules\Jurusan\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Jurusan\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Data master jurusan (satu form simpan untuk tambah & ubah) */
class JurusanController extends Controller
{
    public function index(Request $r)
    {
        $daftar = Jurusan::withCount('kelas')->orderBy('kode')->get();
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('jurusan::index', compact('daftar', 'ubah'));
    }

    public function simpan(Request $r)
    {
        $id = $r->integer('id') ?: null;
        $data = $r->validate([
            'kode' => ['required', 'string', 'max:15', Rule::unique('jurusan')->ignore($id)],
            'nama' => 'required|string|max:120',
        ]);
        Jurusan::updateOrCreate(['id' => $id], $data);
        return redirect()->route('admin.jurusan')->with('ok', 'Jurusan disimpan.');
    }

    public function hapus(Jurusan $jurusan)
    {
        if ($jurusan->kelas()->exists()) return back()->with('error', 'Jurusan masih dipakai kelas.');
        $jurusan->delete();
        return back()->with('ok', 'Jurusan dihapus.');
    }
}
