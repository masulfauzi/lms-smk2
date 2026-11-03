<?php

namespace App\Modules\Diskusi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Diskusi\Models\Diskusi;
use App\Modules\Ruang\Models\Ruang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiskusiController extends Controller
{
    public function index(Request $r, Ruang $ruang)
    {
        $u = $r->user();
        $topik = Diskusi::with(['user', 'balasan.user'])->where('ruang_id', $ruang->id)->whereNull('parent_id')->latest()->paginate(15);
        $pesan = fn ($d) => [
            'id' => $d->id,
            'nama' => $d->user->name,
            'inisial' => $d->user->inisial(),
            'bukan_siswa' => $d->user->role !== 'siswa',
            'guru' => $d->user->role === 'guru',
            'waktu' => $d->created_at->locale('id')->diffForHumans(),
            'isi_html' => teks($d->isi),
            'boleh_hapus' => $d->user_id === $u->id || $u->role !== 'siswa',
        ];
        return Inertia::render('Diskusi/Index', [
            'ruang' => $ruang->kepala(),
            'topik' => $topik->through(fn ($d) => $pesan($d) + ['balasan' => $d->balasan->map($pesan)->values()]),
        ]);
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
