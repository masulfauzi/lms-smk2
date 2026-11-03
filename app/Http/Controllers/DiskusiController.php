<?php

namespace App\Http\Controllers;

use App\Models\Diskusi;
use App\Models\Ruang;
use Illuminate\Http\Request;

class DiskusiController extends Controller
{
    public function index(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas', 'guru');
        $topik = Diskusi::with(['user', 'balasan.user'])->where('ruang_id', $ruang->id)->whereNull('parent_id')->latest()->paginate(15);
        return view('ruang.diskusi', compact('ruang', 'topik'));
    }

    public function store(Request $r, Ruang $ruang)
    {
        $data = $r->validate(['isi' => 'required|string|max:3000', 'parent_id' => 'nullable|integer']);
        if (!empty($data['parent_id'])) {
            $induk = Diskusi::where('ruang_id', $ruang->id)->whereNull('parent_id')->findOrFail($data['parent_id']);
            $data['parent_id'] = $induk->id;
        }
        Diskusi::create($data + ['ruang_id' => $ruang->id, 'user_id' => $r->user()->id]);
        return back()->with('ok', empty($data['parent_id']) ? 'Pertanyaan/diskusi dikirim.' : 'Balasan dikirim.');
    }

    public function destroy(Request $r, Ruang $ruang, Diskusi $diskusi)
    {
        $this->milik($diskusi, $ruang);
        $u = $r->user();
        abort_unless($diskusi->user_id === $u->id || $u->role !== 'siswa', 403);
        $diskusi->delete();
        return back()->with('ok', 'Pesan dihapus.');
    }
}
