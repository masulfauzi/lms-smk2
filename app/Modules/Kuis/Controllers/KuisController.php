<?php

namespace App\Modules\Kuis\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kuis\Models\Kuis;
use App\Modules\Kuis\Models\Percobaan;
use App\Modules\Kuis\Models\Soal;
use App\Modules\Ruang\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class KuisController extends Controller
{
    public function index(Request $r, Ruang $ruang)
    {
        $u = $r->user();
        $ruang->load('mapel', 'kelas');
        $kuis = Kuis::where('ruang_id', $ruang->id)
            ->when($u->role === 'siswa', fn ($q) => $q->where('terbit', true))
            ->withCount(['soal', 'percobaan as selesai_count' => fn ($q) => $q->whereNotNull('selesai_at')])
            ->orderByDesc('mulai')->get();
        $milikSaya = $u->role === 'siswa'
            ? Percobaan::where('siswa_id', $u->id)->whereIn('kuis_id', $kuis->pluck('id'))->get()->keyBy('kuis_id')
            : collect();
        $siswa = $u->role === 'siswa';
        $warna = ['draf' => '', 'belum' => 'biru', 'berlangsung' => 'hijau', 'selesai' => ''];
        $label = ['draf' => 'Draf', 'belum' => 'Belum dibuka', 'berlangsung' => 'Dibuka', 'selesai' => 'Ditutup'];
        return Inertia::render('Kuis/Index', [
            'ruang' => $ruang->kepala(),
            'kuis' => $kuis->map(function ($k) use ($siswa, $milikSaya, $warna, $label) {
                $st = $k->status();
                $p = $milikSaya[$k->id] ?? null;
                $kerjakan = $st === 'berlangsung' && !$p?->selesai_at;
                return [
                    'id' => $k->id, 'judul' => $k->judul, 'berlangsung' => $st === 'berlangsung',
                    'keterangan' => tgl($k->mulai, true) . ' – ' . tgl($k->selesai, true) . ' · ' . $k->durasi_menit . ' menit · ' . $k->soal_count . ' soal',
                    'status' => [$warna[$st], $label[$st]],
                    'hasil' => $siswa && $p?->selesai_at ? ($p->nilai !== null && $k->tampilkan_nilai ? 'Nilai ' . round($p->nilai) : 'Selesai') : null,
                    'kerjakan' => $kerjakan,
                    'selesai_count' => $k->selesai_count,
                ];
            }),
        ]);
    }

    public function create(Ruang $ruang)
    {
        $kuis = new Kuis([
            'mulai' => today()->addDay()->setTime(7, 0),
            'selesai' => today()->addDay()->setTime(21, 0),
            'durasi_menit' => 60, 'acak_soal' => true, 'acak_opsi' => true, 'tampilkan_nilai' => true,
        ]);
        return $this->form($ruang, $kuis);
    }

    /** Halaman form kuis: pengaturan, daftar soal, tambah/ubah soal, impor soal */
    private function form(Ruang $ruang, Kuis $kuis, bool $sudahDikerjakan = false, ?Soal $ubahSoal = null)
    {
        $soal = $kuis->exists ? $kuis->soal : collect();
        return Inertia::render('Kuis/Form', [
            'ruang' => $ruang->kepala(),
            'kuis' => [
                'id' => $kuis->exists ? $kuis->id : null,
                'judul' => $kuis->judul ?? '',
                'petunjuk' => $kuis->petunjuk ?? '',
                'mulai' => $kuis->mulai?->format('Y-m-d\TH:i'),
                'selesai' => $kuis->selesai?->format('Y-m-d\TH:i'),
                'durasi_menit' => $kuis->durasi_menit,
                'acak_soal' => (bool) $kuis->acak_soal,
                'acak_opsi' => (bool) $kuis->acak_opsi,
                'tampilkan_nilai' => (bool) $kuis->tampilkan_nilai,
                'terbit' => (bool) $kuis->terbit,
            ],
            'soal' => $soal->map(fn ($x) => $x->only('id', 'urutan', 'jenis', 'pertanyaan', 'opsi', 'kunci', 'bobot'))->values(),
            'totalBobot' => $soal->sum('bobot'),
            'sudahDikerjakan' => $sudahDikerjakan,
            'ubahSoal' => $ubahSoal?->only('id', 'urutan', 'jenis', 'pertanyaan', 'opsi', 'kunci', 'bobot'),
        ]);
    }

    public function store(Request $r, Ruang $ruang)
    {
        $kuis = Kuis::create($this->validasi($r) + ['ruang_id' => $ruang->id, 'terbit' => false]);
        return redirect()->route('kuis.edit', [$ruang, $kuis])->with('ok', 'Kuis dibuat. Tambahkan soal, lalu terbitkan.');
    }

    public function edit(Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $kuis->load('soal');
        $sudahDikerjakan = $kuis->percobaan()->exists();
        $ubahSoal = $kuis->soal->firstWhere('id', (int) request('soal'));
        return $this->form($ruang, $kuis, $sudahDikerjakan, $ubahSoal);
    }

    public function update(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $data = $this->validasi($r);
        $data['terbit'] = $r->boolean('terbit');
        if ($data['terbit'] && !$kuis->soal()->exists()) {
            return back()->with('error', 'Tambahkan minimal satu soal sebelum kuis diterbitkan.');
        }
        $kuis->update($data);
        return back()->with('ok', $data['terbit'] ? 'Kuis disimpan dan terbit untuk siswa.' : 'Kuis disimpan sebagai draf.');
    }

    public function destroy(Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $kuis->delete();
        return redirect()->route('kuis.index', $ruang)->with('ok', 'Kuis dihapus.');
    }

    public function simpanSoal(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $data = $r->validate([
            'soal_id' => 'nullable|integer',
            'jenis' => 'required|in:pg,esai',
            'pertanyaan' => 'required|string|max:5000',
            'opsi' => 'array',
            'opsi.*' => 'nullable|string|max:1000',
            'kunci' => 'nullable|required_if:jenis,pg|in:A,B,C,D,E',
            'bobot' => 'required|integer|min:1|max:100',
        ], ['kunci.required_if' => 'Pilih kunci jawaban untuk soal pilihan ganda.']);

        $opsi = null;
        if ($data['jenis'] === 'pg') {
            $opsi = array_filter(array_map('trim', $data['opsi'] ?? []), fn ($v) => $v !== '');
            if (count($opsi) < 2) return back()->withInput()->with('error', 'Soal pilihan ganda butuh minimal 2 opsi.');
            if (!isset($opsi[$data['kunci']])) return back()->withInput()->with('error', 'Kunci jawaban harus salah satu opsi yang diisi.');
        }
        $nilai = [
            'jenis' => $data['jenis'], 'pertanyaan' => $data['pertanyaan'], 'opsi' => $opsi,
            'kunci' => $data['jenis'] === 'pg' ? $data['kunci'] : null, 'bobot' => $data['bobot'],
        ];
        if (!empty($data['soal_id'])) {
            $kuis->soal()->whereKey($data['soal_id'])->firstOrFail()->update($nilai);
            $this->hitungUlang($kuis);
            return redirect()->route('kuis.edit', [$ruang, $kuis])->with('ok', 'Soal diperbarui.');
        }
        $kuis->soal()->create($nilai + ['urutan' => (int) $kuis->soal()->max('urutan') + 1]);
        return back()->with('ok', 'Soal ditambahkan.');
    }

    /**
     * Impor banyak soal dari teks, format:
     *   1. Pertanyaan
     *   A. opsi   B. opsi ...
     *   Kunci: B
     * Soal tanpa opsi A-E dianggap esai.
     */
    public function imporSoal(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $r->validate(['teks' => 'required|string|max:100000']);
        $blok = preg_split('/\R(?=\s*\d+[\.\)]\s)/', "\n" . trim($r->teks));
        $urutan = (int) $kuis->soal()->max('urutan');
        $masuk = 0;
        $gagal = [];
        foreach ($blok as $b) {
            $b = trim($b);
            if ($b === '') continue;
            if (!preg_match('/^\s*(\d+)[\.\)]\s*(.*)$/s', $b, $m)) continue;
            $nomor = $m[1];
            $baris = preg_split('/\R/', $m[2]);
            $tanya = [];
            $opsi = [];
            $kunci = null;
            foreach ($baris as $l) {
                if (preg_match('/^\s*([A-Ea-e])[\.\)]\s*(.+)$/', $l, $o)) $opsi[strtoupper($o[1])] = trim($o[2]);
                elseif (preg_match('/^\s*kunci\s*[:=]\s*([A-Ea-e])/i', $l, $k)) $kunci = strtoupper($k[1]);
                elseif (!$opsi) $tanya[] = $l;
            }
            $tanya = trim(implode("\n", $tanya));
            if ($tanya === '') { $gagal[] = $nomor; continue; }
            if ($opsi && (!$kunci || !isset($opsi[$kunci]) || count($opsi) < 2)) { $gagal[] = $nomor; continue; }
            $kuis->soal()->create([
                'urutan' => ++$urutan, 'jenis' => $opsi ? 'pg' : 'esai', 'pertanyaan' => $tanya,
                'opsi' => $opsi ?: null, 'kunci' => $opsi ? $kunci : null, 'bobot' => $opsi ? 1 : 5,
            ]);
            $masuk++;
        }
        $pesan = "$masuk soal berhasil diimpor.";
        if ($gagal) $pesan .= ' Soal nomor ' . implode(', ', $gagal) . ' dilewati (kunci tidak ada / format salah).';
        return back()->with($masuk ? 'ok' : 'error', $pesan);
    }

    public function hapusSoal(Ruang $ruang, Kuis $kuis, Soal $soal)
    {
        $this->milik($kuis, $ruang);
        abort_unless($soal->kuis_id === $kuis->id, 404);
        $soal->delete();
        $kuis->soal()->get()->values()->each(fn ($s, $i) => $s->update(['urutan' => $i + 1]));
        $this->hitungUlang($kuis);
        return back()->with('ok', 'Soal dihapus.');
    }

    public function show(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $u = $r->user();
        $ruang->load('mapel', 'kelas');
        $kuis->load('soal');

        if ($u->role === 'siswa') {
            abort_unless($kuis->terbit, 404);
            $p = $this->tutupBilaHabis(Percobaan::where('kuis_id', $kuis->id)->where('siswa_id', $u->id)->first());
            return Inertia::render('Kuis/Siswa', [
                'ruang' => $ruang->kepala(),
                'kuis' => [
                    'id' => $kuis->id, 'judul' => $kuis->judul, 'status' => $kuis->status(),
                    'jumlah_soal' => $kuis->soal->count(), 'durasi_menit' => $kuis->durasi_menit,
                    'petunjuk_html' => $kuis->petunjuk ? teks($kuis->petunjuk) : null,
                    'mulai' => tgl($kuis->mulai, true), 'selesai' => tgl($kuis->selesai, true),
                ],
                'percobaan' => $p ? [
                    'selesai' => (bool) $p->selesai_at,
                    'nilai' => $kuis->tampilkan_nilai && $p->nilai !== null ? rtrim(rtrim(number_format($p->nilai, 2, ',', ''), '0'), ',') : null,
                    'menunggu' => $p->nilai === null,
                    'tuntas' => $p->nilai !== null && $p->nilai >= $ruang->kkm,
                    'keterangan' => $p->selesai_at ? 'Dikumpulkan ' . tgl($p->selesai_at, true) . ' · ' . count(array_filter($p->jawaban ?? [], fn ($v) => $v !== null && $v !== '')) . ' dari ' . count($p->urutan_soal['soal']) . ' soal dijawab' : null,
                ] : null,
            ]);
        }

        $siswa = $ruang->siswa()->get();
        $percobaan = Percobaan::where('kuis_id', $kuis->id)->get()->map(fn ($p) => $this->tutupBilaHabis($p))->keyBy('siswa_id');
        $selesai = $percobaan->whereNotNull('selesai_at');
        // Analisis butir: persentase siswa yang menjawab benar tiap soal PG
        $butir = [];
        foreach ($kuis->soal->where('jenis', 'pg') as $s) {
            $benar = $selesai->filter(fn ($p) => ($p->jawaban[$s->id] ?? null) === $s->kunci)->count();
            $sebaran = $selesai->countBy(fn ($p) => $p->jawaban[$s->id] ?? '-');
            $butir[$s->id] = ['persen' => $selesai->count() ? round($benar / $selesai->count() * 100) : null, 'sebaran' => $sebaran];
        }
        $selesaiSemua = $percobaan->whereNotNull('selesai_at');
        $dinilai = $selesaiSemua->whereNotNull('nilai');
        $st = $kuis->status();
        $ada = $dinilai->count();
        return Inertia::render('Kuis/Guru', [
            'ruang' => $ruang->kepala(),
            'kuis' => ['id' => $kuis->id, 'judul' => $kuis->judul, 'periode' => tgl($kuis->mulai, true) . ' – ' . tgl($kuis->selesai, true)],
            'stat' => [
                'status' => ['draf' => 'Draf', 'belum' => 'Belum dibuka', 'berlangsung' => 'Sedang dibuka', 'selesai' => 'Ditutup'][$st],
                'mengumpulkan' => $selesaiSemua->count() . '/' . $siswa->count(),
                'mengerjakan' => $percobaan->count() - $selesaiSemua->count(),
                'rata' => $ada ? round($dinilai->avg('nilai'), 1) : '-',
                'tertinggi' => $ada ? round($dinilai->max('nilai'), 1) : '-',
                'terendah' => $ada ? round($dinilai->min('nilai'), 1) : '-',
                'tuntas' => $dinilai->where('nilai', '>=', $ruang->kkm)->count(),
                'menunggu' => $selesaiSemua->whereNull('nilai')->count(),
            ],
            'hasil' => $siswa->values()->map(function ($s) use ($percobaan, $st, $ruang) {
                $p = $percobaan[$s->id] ?? null;
                return [
                    'id' => $s->id, 'nama' => $s->name, 'percobaan_id' => $p?->id,
                    'status' => match (true) {
                        !$p => [$st === 'selesai' ? 'merah' : '', $st === 'selesai' ? 'Tidak mengerjakan' : 'Belum'],
                        !$p->selesai_at => ['biru', 'Mengerjakan'],
                        $p->nilai === null => ['kuning', 'Perlu koreksi'],
                        default => ['hijau', 'Selesai'],
                    },
                    'waktu' => $p?->selesai_at ? ((string) $p->mulai_at->diffInMinutes($p->selesai_at)) . ' menit' : '-',
                    'nilai' => $p && $p->nilai !== null ? round($p->nilai, 1) : null,
                    'rendah' => $p && $p->nilai !== null && $p->nilai < $ruang->kkm,
                    'selesai' => (bool) $p?->selesai_at,
                    'perlu_koreksi' => $p?->nilai === null,
                ];
            }),
            'butir' => $kuis->soal->where('jenis', 'pg')->values()->map(fn ($x) => [
                'id' => $x->id,
                'judul' => $x->urutan . '. ' . Str::limit($x->pertanyaan, 50),
                'pertanyaan' => $x->pertanyaan,
                'persen' => $butir[$x->id]['persen'],
                'sebaran' => $butir[$x->id]['sebaran']->sortKeys()->map(fn ($n, $h) => $h . ': ' . $n . ($h === $x->kunci ? ' (kunci)' : ''))->values(),
            ]),
        ]);
    }

    public function mulai(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $u = $r->user();
        if ($kuis->status() !== 'berlangsung') return back()->with('error', 'Kuis tidak sedang dibuka.');
        $p = Percobaan::where('kuis_id', $kuis->id)->where('siswa_id', $u->id)->first();
        if (!$p) {
            $soal = $kuis->soal()->get();
            $urut = $kuis->acak_soal ? $soal->shuffle() : $soal;
            $opsi = [];
            foreach ($urut as $s) {
                if ($s->jenis !== 'pg') continue;
                $huruf = array_keys($s->opsi ?? []);
                if ($kuis->acak_opsi) shuffle($huruf);
                $opsi[$s->id] = $huruf;
            }
            $p = Percobaan::create([
                'kuis_id' => $kuis->id, 'siswa_id' => $u->id, 'mulai_at' => now(),
                'batas_at' => now()->addMinutes($kuis->durasi_menit)->min($kuis->selesai),
                'urutan_soal' => ['soal' => $urut->pluck('id')->all(), 'opsi' => $opsi], 'jawaban' => [],
            ]);
        }
        return redirect()->route('kuis.kerjakan', [$ruang, $kuis]);
    }

    public function kerjakan(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $p = $this->tutupBilaHabis(Percobaan::where('kuis_id', $kuis->id)->where('siswa_id', $r->user()->id)->firstOrFail());
        if ($p->selesai_at) return redirect()->route('kuis.show', [$ruang, $kuis]);
        $soal = $kuis->soal()->get()->keyBy('id');
        $daftar = collect($p->urutan_soal['soal'])->map(fn ($id) => $soal[$id] ?? null)->filter()->values();
        return Inertia::render('Kuis/Kerjakan', [
            'ruang' => $ruang->kepala(),
            'kuis' => ['id' => $kuis->id, 'judul' => $kuis->judul],
            'batas' => $p->batas_at->toIso8601String(),
            'sekarangMs' => now()->getTimestampMs(),
            'soal' => $daftar->map(fn ($x) => [
                'id' => $x->id,
                'jenis' => $x->jenis,
                'pertanyaan' => $x->pertanyaan,
                'opsi' => $x->jenis === 'pg'
                    ? collect($p->urutan_soal['opsi'][$x->id] ?? array_keys($x->opsi))->map(fn ($h) => ['huruf' => $h, 'teks' => $x->opsi[$h]])->values()
                    : [],
                'jawaban' => $p->jawaban[$x->id] ?? null,
            ]),
        ]);
    }

    /** Simpan otomatis satu jawaban (dipanggil JavaScript setiap siswa memilih / mengetik) */
    public function jawab(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $data = $r->validate(['soal_id' => 'required|integer', 'jawaban' => 'nullable|string|max:20000']);
        return DB::transaction(function () use ($r, $kuis, $data) {
            $p = Percobaan::where('kuis_id', $kuis->id)->where('siswa_id', $r->user()->id)->lockForUpdate()->firstOrFail();
            if ($p->selesai_at || now()->gt($p->batas_at)) return response()->json(['ok' => false, 'pesan' => 'Waktu habis'], 409);
            if (!in_array($data['soal_id'], $p->urutan_soal['soal'], true)) return response()->json(['ok' => false], 422);
            $jawab = $p->jawaban ?? [];
            $jawab[$data['soal_id']] = $data['jawaban'];
            $p->update(['jawaban' => $jawab]);
            return response()->json(['ok' => true, 'tersimpan' => now()->format('H.i.s')]);
        });
    }

    public function kumpul(Request $r, Ruang $ruang, Kuis $kuis)
    {
        $this->milik($kuis, $ruang);
        $p = Percobaan::where('kuis_id', $kuis->id)->where('siswa_id', $r->user()->id)->firstOrFail();
        if (!$p->selesai_at) {
            $p->selesai_at = now()->min($p->batas_at);
            $p->nilai = $p->hitungNilai();
            $p->save();
        }
        return redirect()->route('kuis.show', [$ruang, $kuis])->with('ok', 'Jawaban terkumpul. Terima kasih!');
    }

    public function koreksi(Ruang $ruang, Kuis $kuis, Percobaan $percobaan)
    {
        $this->milik($kuis, $ruang);
        abort_unless($percobaan->kuis_id === $kuis->id, 404);
        $kuis->load('soal');
        $percobaan->load('siswa');
        return Inertia::render('Kuis/Koreksi', [
            'ruang' => $ruang->kepala(),
            'kuis' => ['id' => $kuis->id, 'judul' => $kuis->judul],
            'percobaan' => [
                'id' => $percobaan->id, 'nama' => $percobaan->siswa->name,
                'keterangan' => 'Dikumpulkan ' . tgl($percobaan->selesai_at, true) . ' · nilai saat ini ' . ($percobaan->nilai !== null ? round($percobaan->nilai, 1) : 'menunggu koreksi esai'),
            ],
            'soal' => $kuis->soal->map(function ($x) use ($percobaan) {
                $j = $percobaan->jawaban[$x->id] ?? null;
                return [
                    'id' => $x->id, 'urutan' => $x->urutan, 'jenis' => $x->jenis, 'pertanyaan' => $x->pertanyaan, 'bobot' => $x->bobot, 'kunci' => $x->kunci,
                    'jawaban_pg' => $x->jenis === 'pg' ? ($j ? $j . '. ' . ($x->opsi[$j] ?? '') : 'Tidak dijawab') : null,
                    'benar' => $x->jenis === 'pg' && $j === $x->kunci,
                    'jawaban_html' => $x->jenis === 'esai' && filled($j) ? teks($j) : null,
                    'skor' => $percobaan->nilai_esai[$x->id] ?? '',
                ];
            }),
        ]);
    }

    public function simpanKoreksi(Request $r, Ruang $ruang, Kuis $kuis, Percobaan $percobaan)
    {
        $this->milik($kuis, $ruang);
        abort_unless($percobaan->kuis_id === $kuis->id, 404);
        $skor = [];
        foreach ($kuis->soal->where('jenis', 'esai') as $s) {
            $v = $r->input("skor.{$s->id}");
            if ($v === null || $v === '') continue;
            $skor[$s->id] = max(0, min($s->bobot, (float) $v));
        }
        $percobaan->nilai_esai = $skor;
        $percobaan->nilai = $percobaan->hitungNilai();
        $percobaan->save();
        $berikut = Percobaan::where('kuis_id', $kuis->id)->whereNotNull('selesai_at')->whereNull('nilai')->first();
        return $berikut
            ? redirect()->route('kuis.koreksi', [$ruang, $kuis, $berikut])->with('ok', 'Koreksi disimpan. Lanjut ke siswa berikutnya.')
            : redirect()->route('kuis.show', [$ruang, $kuis])->with('ok', 'Koreksi disimpan.');
    }

    public function resetPercobaan(Ruang $ruang, Kuis $kuis, Percobaan $percobaan)
    {
        $this->milik($kuis, $ruang);
        abort_unless($percobaan->kuis_id === $kuis->id, 404);
        $percobaan->delete();
        return back()->with('ok', 'Percobaan dihapus. Siswa bisa mengerjakan ulang selama kuis masih dibuka.');
    }

    /** Percobaan yang waktunya habis tapi belum dikumpulkan ditutup otomatis */
    private function tutupBilaHabis(?Percobaan $p): ?Percobaan
    {
        if ($p && !$p->selesai_at && now()->gte($p->batas_at)) {
            $p->selesai_at = $p->batas_at;
            $p->nilai = $p->hitungNilai();
            $p->save();
        }
        return $p;
    }

    private function hitungUlang(Kuis $kuis): void
    {
        $kuis->unsetRelation('soal');
        foreach ($kuis->percobaan()->whereNotNull('selesai_at')->get() as $p) {
            $p->setRelation('kuis', $kuis);
            $p->update(['nilai' => $p->hitungNilai()]);
        }
    }

    private function validasi(Request $r): array
    {
        return $r->validate([
            'judul' => 'required|string|max:160',
            'petunjuk' => 'nullable|string|max:5000',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after:mulai',
            'durasi_menit' => 'required|integer|min:5|max:600',
        ], ['selesai.after' => 'Waktu tutup harus setelah waktu buka.']) + [
            'acak_soal' => $r->boolean('acak_soal'),
            'acak_opsi' => $r->boolean('acak_opsi'),
            'tampilkan_nilai' => $r->boolean('tampilkan_nilai'),
        ];
    }
}
