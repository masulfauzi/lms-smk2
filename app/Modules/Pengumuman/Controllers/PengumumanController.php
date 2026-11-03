<?php

namespace App\Modules\Pengumuman\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Kelas\Models\Kelas;
use App\Modules\Pengumuman\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $daftar = Pengumuman::untuk($u)->with('kelas')->paginate(10);
        $kelas = match ($u->role) {
            'admin' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'guru' => Kelas::whereIn('id', $u->ruangQuery()->pluck('kelas_id'))->orderBy('tingkat')->orderBy('nama')->get(),
            default => collect(),
        };
        return view('pengumuman::index', compact('daftar', 'kelas'));
    }

    public function store(Request $r)
    {
        $u = $r->user();
        $data = $r->validate([
            'judul' => 'required|string|max:160',
            'isi' => 'required|string|max:5000',
            'sasaran' => 'required|in:semua,guru,siswa',
            'kelas_id' => 'nullable|exists:kelas,id',
            'disematkan' => 'nullable|boolean',
        ]);
        if ($u->role === 'guru') {
            // Guru hanya mengumumkan ke siswa di kelas yang ia ajar
            abort_unless($data['kelas_id'] && $u->ruangQuery()->where('kelas_id', $data['kelas_id'])->exists(), 403);
            $data['sasaran'] = 'siswa';
        }
        if (!empty($data['kelas_id'])) $data['sasaran'] = 'siswa';
        $data['disematkan'] = $u->role === 'admin' && $r->boolean('disematkan');
        Pengumuman::create($data + ['user_id' => $u->id]);
        return back()->with('ok', 'Pengumuman diterbitkan.');
    }

    public function destroy(Request $r, Pengumuman $pengumuman)
    {
        abort_unless($r->user()->role === 'admin' || $pengumuman->user_id === $r->user()->id, 403);
        $pengumuman->delete();
        return back()->with('ok', 'Pengumuman dihapus.');
    }
}
