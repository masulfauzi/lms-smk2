<?php

namespace App\Modules\Mapel\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Mapel\Models\Mapel;
use App\Modules\Ruang\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Data master mata pelajaran (satu form simpan untuk tambah & ubah) */
class MapelController extends Controller
{
    public function index(Request $r)
    {
        $daftar = Mapel::orderBy('kelompok')->orderBy('nama')->get();
        $dipakai = Ruang::selectRaw('mapel_id, count(*) n')->groupBy('mapel_id')->pluck('n', 'mapel_id');
        $ubah = $daftar->firstWhere('id', (int) $r->query('ubah'));
        return view('mapel::index', compact('daftar', 'dipakai', 'ubah'));
    }

    public function simpan(Request $r)
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

    public function hapus(Mapel $mapel)
    {
        if (Ruang::where('mapel_id', $mapel->id)->exists()) return back()->with('error', 'Mapel masih dipakai di ruang belajar.');
        $mapel->delete();
        return back()->with('ok', 'Mata pelajaran dihapus.');
    }
}
