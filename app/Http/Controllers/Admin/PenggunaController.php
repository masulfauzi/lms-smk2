<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $r, string $role)
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')->get();
        $q = User::with('kelas')->where('role', $role)->orderBy('name');
        if ($r->filled('cari')) {
            $c = '%' . $r->query('cari') . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $c)->orWhere('username', 'like', $c));
        }
        if ($role === 'siswa' && $r->filled('kelas')) $q->where('kelas_id', $r->query('kelas'));
        $daftar = $q->paginate(25)->withQueryString();
        $ubah = $r->filled('ubah') ? User::where('role', $role)->find($r->query('ubah')) : null;
        return view('admin.pengguna', compact('role', 'daftar', 'kelas', 'ubah'));
    }

    public function simpan(Request $r, string $role)
    {
        $id = $r->integer('id') ?: null;
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users')->ignore($id)],
            'email' => 'nullable|email|max:120',
            'jk' => 'nullable|in:L,P',
            'kelas_id' => [$role === 'siswa' ? 'required' : 'nullable', 'nullable', 'exists:kelas,id'],
            'password' => [$id ? 'nullable' : 'required', 'nullable', 'string', 'min:6'],
            'aktif' => 'nullable|boolean',
        ], ['username.regex' => 'NIP/NISN hanya boleh huruf, angka, titik, minus, garis bawah.']);
        $data['aktif'] = $r->boolean('aktif');
        if ($role !== 'siswa') $data['kelas_id'] = null;
        if (empty($data['password'])) unset($data['password']);
        User::updateOrCreate(['id' => $id], $data + ['role' => $role]);
        return redirect()->route('admin.pengguna', $role)->with('ok', ucfirst($role) . ' disimpan.');
    }

    /**
     * Impor CSV (pemisah ; atau ,). Kolom: nama;nip/nisn;jk;kelas;password
     * Baris dengan NIP/NISN yang sudah ada akan diperbarui. Password kosong = sama dengan NIP/NISN.
     */
    public function impor(Request $r, string $role)
    {
        $r->validate(['csv' => 'required|file|max:2048|mimes:csv,txt']);
        $isi = file_get_contents($r->file('csv')->getRealPath());
        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi);
        $baris = preg_split('/\R/', trim($isi));
        $pemisah = substr_count($baris[0] ?? '', ';') >= substr_count($baris[0] ?? '', ',') ? ';' : ',';
        $kelas = Kelas::pluck('id', 'nama')->mapWithKeys(fn ($id, $n) => [mb_strtolower(trim($n)) => $id]);
        $baru = $ubah = 0;
        $galat = [];
        DB::transaction(function () use ($baris, $pemisah, $kelas, $role, &$baru, &$ubah, &$galat) {
            foreach ($baris as $i => $l) {
                $k = array_map('trim', str_getcsv($l, $pemisah));
                if ($i === 0 && preg_match('/nama/i', $k[0] ?? '')) continue; // baris judul
                if (count(array_filter($k)) === 0) continue;
                [$nama, $nomor, $jk, $namaKelas, $pass] = array_pad($k, 5, '');
                if ($nama === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $nomor)) { $galat[] = $i + 1; continue; }
                $kelasId = $role === 'siswa' ? ($kelas[mb_strtolower($namaKelas)] ?? null) : null;
                if ($role === 'siswa' && !$kelasId) { $galat[] = $i + 1; continue; }
                $u = User::where('username', $nomor)->first();
                if ($u && $u->role !== $role) { $galat[] = $i + 1; continue; }
                $nilai = ['name' => $nama, 'jk' => in_array(strtoupper($jk), ['L', 'P']) ? strtoupper($jk) : null, 'kelas_id' => $kelasId, 'role' => $role];
                if ($u) {
                    $u->update($nilai + ($pass !== '' ? ['password' => $pass] : []));
                    $ubah++;
                } else {
                    User::create($nilai + ['username' => $nomor, 'password' => $pass !== '' ? $pass : $nomor, 'aktif' => true]);
                    $baru++;
                }
            }
        });
        $pesan = "$baru data baru, $ubah diperbarui.";
        if ($galat) $pesan .= ' Baris ' . implode(', ', array_slice($galat, 0, 15)) . (count($galat) > 15 ? ', ...' : '') . ' dilewati (NIP/NISN tidak valid, kelas tidak ditemukan, atau bentrok role).';
        return back()->with($baru + $ubah ? 'ok' : 'error', $pesan);
    }

    public function contohCsv(string $role)
    {
        $isi = $role === 'siswa'
            ? "nama;nisn;jk;kelas;password\nAhmad Fauzi;0081234567;L;X TKJ 1;\nSiti Aminah;0081234568;P;X TKJ 1;\n"
            : "nama;nip;jk;kelas;password\nBudi Santoso, S.Pd.;198701012010011001;L;;\n";
        return response("\u{FEFF}" . $isi, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="contoh-impor-' . $role . '.csv"',
        ]);
    }

    public function resetPassword(User $user)
    {
        abort_if($user->role === 'admin', 403);
        $user->update(['password' => $user->username]);
        return back()->with('ok', 'Password ' . $user->name . ' direset menjadi NIP/NISN-nya.');
    }

    public function hapus(Request $r, User $user)
    {
        abort_if($user->role === 'admin' || $user->id === $r->user()->id, 403);
        $user->delete();
        return back()->with('ok', $user->name . ' dihapus beserta seluruh datanya.');
    }
}
