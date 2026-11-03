@extends('layouts.app')
@section('title', ($kuis->exists ? 'Ubah' : 'Buat') . ' kuis')
@section('content')
@include('ruang::_kepala')
@php($sudahDikerjakan = $sudahDikerjakan ?? false)
@php($ubahSoal = $ubahSoal ?? null)
<div class="grid g-utama">
  <div>
    @if ($kuis->exists)
      <div class="kartu rapat">
        <h2>{!! icon('quiz', 18) !!}Soal <span class="kanan kecil-redup">{{ $kuis->soal->count() }} soal · total bobot {{ $kuis->soal->sum('bobot') }}</span></h2>
        @if ($sudahDikerjakan)<p class="flash error" style="margin:12px 20px 0">Sudah ada siswa yang mengerjakan. Mengubah kunci/bobot akan menghitung ulang nilai mereka.</p>@endif
        <div style="padding:6px 20px 16px">
          @forelse ($kuis->soal as $s)
            <div style="padding:14px 0;border-bottom:1px dashed var(--garis-2)">
              <div style="display:flex;gap:10px;align-items:flex-start">
                <b style="color:var(--tinta)">{{ $s->urutan }}.</b>
                <div style="flex:1;min-width:0">
                  <div style="white-space:pre-line">{{ $s->pertanyaan }}</div>
                  @if ($s->jenis === 'pg')
                    <div style="display:grid;gap:4px;margin-top:8px;font-size:14px">
                      @foreach ($s->opsi as $h => $o)<div style="{{ $h === $s->kunci ? 'color:var(--hijau);font-weight:700' : 'color:var(--redup)' }}">{{ $h }}. {{ $o }} @if ($h === $s->kunci){!! icon('check', 14) !!}@endif</div>@endforeach
                    </div>
                  @endif
                  <div style="margin-top:6px"><span class="lencana {{ $s->jenis === 'pg' ? 'biru' : 'kuning' }}">{{ $s->jenis === 'pg' ? 'Pilihan ganda' : 'Esai' }}</span> <span class="lencana">Bobot {{ $s->bobot }}</span></div>
                </div>
                <a class="ikon-btn" href="?soal={{ $s->id }}#form-soal" title="Ubah">{!! icon('edit', 16) !!}</a>
                @include('partials.hapus', ['url' => route('kuis.soal.hapus', [$ruang, $kuis, $s]), 'pesan' => 'Hapus soal nomor ' . $s->urutan . '?'])
              </div>
            </div>
          @empty
            @include('partials.kosong', ['ikon' => 'quiz', 'judul' => 'Belum ada soal', 'teks' => 'Tambah satu per satu atau impor sekaligus dari teks.'])
          @endforelse
        </div>
      </div>

      <form method="post" action="{{ route('kuis.soal', [$ruang, $kuis]) }}" class="kartu" id="form-soal">
        @csrf
        <h2>{!! icon($ubahSoal ? 'edit' : 'plus', 18) !!}{{ $ubahSoal ? 'Ubah soal nomor ' . $ubahSoal->urutan : 'Tambah soal' }}</h2>
        @if ($ubahSoal)<input type="hidden" name="soal_id" value="{{ $ubahSoal->id }}">@endif
        @php($jenis = old('jenis', $ubahSoal->jenis ?? 'pg'))
        <div class="baris-form">
          <div class="field"><label>Jenis</label><select name="jenis" id="jenis"><option value="pg" @selected($jenis === 'pg')>Pilihan ganda (dinilai otomatis)</option><option value="esai" @selected($jenis === 'esai')>Esai (dikoreksi guru)</option></select></div>
          <div class="field" style="max-width:120px"><label>Bobot</label><input type="number" name="bobot" min="1" max="100" value="{{ old('bobot', $ubahSoal->bobot ?? 1) }}" required></div>
        </div>
        <div class="field"><label>Pertanyaan</label><textarea name="pertanyaan" required style="min-height:90px">{{ old('pertanyaan', $ubahSoal->pertanyaan ?? '') }}</textarea></div>
        <div id="area-opsi">
          @foreach (['A', 'B', 'C', 'D', 'E'] as $h)
            <div class="inline-form" style="margin-bottom:8px">
              <label class="cek" style="width:58px"><input type="radio" name="kunci" value="{{ $h }}" @checked(old('kunci', $ubahSoal->kunci ?? '') === $h)> {{ $h }}</label>
              <div class="field" style="flex:1"><input type="text" name="opsi[{{ $h }}]" value="{{ old('opsi.' . $h, $ubahSoal->opsi[$h] ?? '') }}" placeholder="Opsi {{ $h }}{{ $h === 'E' ? ' (opsional)' : '' }}"></div>
            </div>
          @endforeach
          <small class="kecil-redup">Pilih bulatan di kiri sebagai kunci jawaban.</small>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px">
          <button class="btn">{!! icon('check', 16) !!}{{ $ubahSoal ? 'Simpan perubahan' : 'Tambah soal' }}</button>
          @if ($ubahSoal)<a class="btn garis" href="{{ route('kuis.edit', [$ruang, $kuis]) }}">Batal</a>@endif
        </div>
      </form>

      <form method="post" action="{{ route('kuis.impor', [$ruang, $kuis]) }}" class="kartu">
        @csrf
        <h2>{!! icon('upload', 18) !!}Impor soal dari teks</h2>
        <p class="kecil-redup">Tempel soal dari Word/Google Docs dengan format di bawah. Soal tanpa opsi dianggap esai.</p>
        <textarea name="teks" class="besar" placeholder="1. Ibu kota provinsi Jawa Tengah adalah ...
A. Surabaya
B. Semarang
C. Bandung
D. Yogyakarta
Kunci: B

2. Jelaskan pengertian fotosintesis!"></textarea>
        <button class="btn garis mt">{!! icon('upload', 16) !!}Impor soal</button>
      </form>
    @endif
  </div>

  <form method="post" action="{{ $kuis->exists ? route('kuis.update', [$ruang, $kuis]) : route('kuis.store', $ruang) }}" class="kartu">
    @csrf @if ($kuis->exists) @method('put') @endif
    <h2>{!! icon('settings', 18) !!}Pengaturan kuis</h2>
    <div class="field"><label>Judul</label><input type="text" name="judul" value="{{ old('judul', $kuis->judul) }}" required maxlength="160" placeholder="Contoh: Ulangan Harian Bab 2"></div>
    <div class="field"><label>Petunjuk</label><textarea name="petunjuk" style="min-height:80px" placeholder="Kerjakan dengan jujur. Waktu berjalan sejak tombol Mulai ditekan.">{{ old('petunjuk', $kuis->petunjuk) }}</textarea></div>
    <div class="field"><label>Dibuka</label><input type="datetime-local" name="mulai" value="{{ old('mulai', $kuis->mulai?->format('Y-m-d\TH:i')) }}" required></div>
    <div class="field"><label>Ditutup</label><input type="datetime-local" name="selesai" value="{{ old('selesai', $kuis->selesai?->format('Y-m-d\TH:i')) }}" required></div>
    <div class="field"><label>Durasi mengerjakan (menit)</label><input type="number" name="durasi_menit" min="5" max="600" value="{{ old('durasi_menit', $kuis->durasi_menit) }}" required><small>Waktu tetap berhenti saat kuis ditutup.</small></div>
    <div class="field"><label class="cek"><input type="checkbox" name="acak_soal" value="1" @checked(old('acak_soal', $kuis->acak_soal))> Acak urutan soal</label></div>
    <div class="field"><label class="cek"><input type="checkbox" name="acak_opsi" value="1" @checked(old('acak_opsi', $kuis->acak_opsi))> Acak urutan opsi jawaban</label></div>
    <div class="field"><label class="cek"><input type="checkbox" name="tampilkan_nilai" value="1" @checked(old('tampilkan_nilai', $kuis->tampilkan_nilai))> Tampilkan nilai ke siswa setelah selesai</label></div>
    @if ($kuis->exists)
      <div class="field catatan" style="border-left-color:var(--stabilo)"><label class="cek"><input type="checkbox" name="terbit" value="1" @checked($kuis->terbit)> Terbitkan untuk siswa</label></div>
    @endif
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn">{!! icon('check', 16) !!}{{ $kuis->exists ? 'Simpan' : 'Buat kuis & isi soal' }}</button>
      @if ($kuis->exists)<a class="btn garis" href="{{ route('kuis.show', [$ruang, $kuis]) }}">Lihat hasil</a>@endif
    </div>
  </form>
</div>
@if ($kuis->exists)
  <div class="mt">@include('partials.hapus', ['url' => route('kuis.destroy', [$ruang, $kuis]), 'pesan' => 'Hapus kuis ini beserta semua jawaban siswa?', 'kelas' => 'btn bahaya kecil', 'label' => 'Hapus kuis'])</div>
@endif
@push('script')
<script>
(function () {
  var j = document.getElementById('jenis'), a = document.getElementById('area-opsi');
  if (!j) return;
  function atur() { a.style.display = j.value === 'pg' ? '' : 'none'; }
  j.addEventListener('change', atur); atur();
})();
</script>
@endpush
@endsection
