<?php

namespace App\Http\Controllers;

use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PertemuanController extends Controller
{
    private const LAMPIRAN = 'nullable|file|max:20480|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,txt';

    public function create(Ruang $ruang)
    {
        $ruang->load('mapel', 'kelas');
        $pertemuan = new Pertemuan([
            'urutan' => (int) Pertemuan::where('ruang_id', $ruang->id)->max('urutan') + 1,
            'tanggal' => today(),
            'terbit' => true,
        ]);
        return view('pertemuan.form', compact('ruang', 'pertemuan'));
    }

    public function store(Request $r, Ruang $ruang)
    {
        $data = $this->validasi($r);
        $p = new Pertemuan($data + ['ruang_id' => $ruang->id]);
        $this->lampiran($r, $p, $ruang);
        $p->save();
        return redirect()->route('pertemuan.show', [$ruang, $p])->with('ok', 'Pertemuan ' . $p->urutan . ' disimpan.');
    }

    public function edit(Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        $ruang->load('mapel', 'kelas');
        return view('pertemuan.form', compact('ruang', 'pertemuan'));
    }

    public function update(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        $pertemuan->fill($this->validasi($r));
        if ($r->boolean('hapus_lampiran') && $pertemuan->lampiran_path) {
            Storage::delete($pertemuan->lampiran_path);
            $pertemuan->lampiran_path = $pertemuan->lampiran_nama = null;
        }
        $this->lampiran($r, $pertemuan, $ruang);
        $pertemuan->save();
        return redirect()->route('pertemuan.show', [$ruang, $pertemuan])->with('ok', 'Pertemuan diperbarui.');
    }

    public function destroy(Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        if ($pertemuan->lampiran_path) Storage::delete($pertemuan->lampiran_path);
        $pertemuan->delete();
        return redirect()->route('ruang.show', $ruang)->with('ok', 'Pertemuan dihapus.');
    }

    public function show(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        $u = $r->user();
        abort_if($u->role === 'siswa' && !$pertemuan->terbit, 404);
        $ruang->load('mapel', 'kelas', 'guru');
        $pertemuan->load('tugas');

        $presensiSaya = null;
        $selesai = false;
        $rekap = collect();
        $siswa = collect();
        if ($u->role === 'siswa') {
            $presensiSaya = Presensi::where('pertemuan_id', $pertemuan->id)->where('siswa_id', $u->id)->first();
            $selesai = DB::table('materi_selesai')->where('pertemuan_id', $pertemuan->id)->where('siswa_id', $u->id)->exists();
        } else {
            $siswa = $ruang->siswa()->get();
            $rekap = Presensi::where('pertemuan_id', $pertemuan->id)->get()->keyBy('siswa_id');
        }
        $sebelum = Pertemuan::where('ruang_id', $ruang->id)->where('urutan', '<', $pertemuan->urutan)
            ->when($u->role === 'siswa', fn ($q) => $q->where('terbit', true))->orderByDesc('urutan')->first();
        $sesudah = Pertemuan::where('ruang_id', $ruang->id)->where('urutan', '>', $pertemuan->urutan)
            ->when($u->role === 'siswa', fn ($q) => $q->where('terbit', true))->orderBy('urutan')->first();

        return view('pertemuan.show', compact('ruang', 'pertemuan', 'presensiSaya', 'selesai', 'rekap', 'siswa', 'sebelum', 'sesudah'));
    }

    /** Siswa menyatakan hadir selama jendela presensi terbuka */
    public function hadir(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        if (!$pertemuan->terbit || !$pertemuan->presensiTerbuka()) {
            return back()->with('error', 'Presensi untuk pertemuan ini sedang tidak dibuka.');
        }
        $p = Presensi::firstOrCreate(
            ['pertemuan_id' => $pertemuan->id, 'siswa_id' => $r->user()->id],
            ['status' => 'hadir', 'waktu' => now()]
        );
        return back()->with('ok', $p->wasRecentlyCreated ? 'Kehadiran tercatat pukul ' . now()->format('H.i') . '.' : 'Presensi kamu sudah tercatat sebelumnya.');
    }

    public function selesai(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        abort_unless($pertemuan->terbit, 404);
        $kunci = ['pertemuan_id' => $pertemuan->id, 'siswa_id' => $r->user()->id];
        if (DB::table('materi_selesai')->where($kunci)->exists()) {
            DB::table('materi_selesai')->where($kunci)->delete();
            return back()->with('ok', 'Tanda selesai dibatalkan.');
        }
        DB::table('materi_selesai')->insert($kunci + ['selesai_at' => now()]);
        return back()->with('ok', 'Materi ditandai selesai dipelajari.');
    }

    /** Guru mengisi / mengubah status presensi (izin, sakit, alpa, hadir) */
    public function aturPresensi(Request $r, Ruang $ruang, Pertemuan $pertemuan)
    {
        $this->milik($pertemuan, $ruang);
        $data = $r->validate(['status' => 'array', 'status.*' => 'nullable|in:hadir,izin,sakit,alpa', 'ket' => 'array', 'ket.*' => 'nullable|string|max:255']);
        $siswaIds = $ruang->siswa()->pluck('id')->all();
        foreach ($data['status'] ?? [] as $siswaId => $status) {
            if (!in_array((int) $siswaId, $siswaIds, true)) continue;
            $kunci = ['pertemuan_id' => $pertemuan->id, 'siswa_id' => $siswaId];
            if (!$status) {
                Presensi::where($kunci)->delete();
                continue;
            }
            $lama = Presensi::where($kunci)->first();
            if ($lama && $lama->status === $status && $lama->keterangan === ($data['ket'][$siswaId] ?? null)) continue;
            Presensi::updateOrCreate($kunci, [
                'status' => $status,
                'keterangan' => $data['ket'][$siswaId] ?? null,
                'waktu' => $lama?->waktu ?? now(),
                'oleh_guru' => true,
            ]);
        }
        return back()->with('ok', 'Presensi pertemuan ' . $pertemuan->urutan . ' disimpan.');
    }

    private function validasi(Request $r): array
    {
        $data = $r->validate([
            'urutan' => 'required|integer|min:1|max:500',
            'judul' => 'required|string|max:160',
            'isi' => 'nullable|string|max:60000',
            'video_url' => ['nullable', 'url', 'max:255', 'regex:~(youtube\.com|youtu\.be)~i'],
            'tanggal' => 'required|date',
            'presensi' => 'nullable|boolean',
            'jam_buka' => 'nullable|required_if:presensi,1|date_format:H:i',
            'jam_tutup' => 'nullable|required_if:presensi,1|date_format:H:i|after:jam_buka',
            'terbit' => 'nullable|boolean',
            'lampiran' => self::LAMPIRAN,
        ], [
            'video_url.regex' => 'Link video harus dari YouTube.',
            'jam_tutup.after' => 'Jam tutup presensi harus setelah jam buka.',
        ]);
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();
        return [
            'urutan' => $data['urutan'],
            'judul' => $data['judul'],
            'isi' => $data['isi'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'tanggal' => $tanggal,
            'presensi_buka' => $r->boolean('presensi') ? "$tanggal {$data['jam_buka']}:00" : null,
            'presensi_tutup' => $r->boolean('presensi') ? "$tanggal {$data['jam_tutup']}:00" : null,
            'terbit' => $r->boolean('terbit'),
        ];
    }

    private function lampiran(Request $r, Pertemuan $p, Ruang $ruang): void
    {
        if (!$r->hasFile('lampiran')) return;
        if ($p->lampiran_path) Storage::delete($p->lampiran_path);
        $f = $r->file('lampiran');
        $p->lampiran_path = $f->store("materi/ruang-{$ruang->id}");
        $p->lampiran_nama = $f->getClientOriginalName();
    }
}
