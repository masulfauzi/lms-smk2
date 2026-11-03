@extends('layouts.app')
@section('title', ($pertemuan->exists ? 'Ubah' : 'Tambah') . ' pertemuan')
@section('content')
@include('partials.ruang-kepala')
<form method="post" enctype="multipart/form-data" action="{{ $pertemuan->exists ? route('pertemuan.update', [$ruang, $pertemuan]) : route('pertemuan.store', $ruang) }}" class="grid g-utama">
  @csrf @if ($pertemuan->exists) @method('put') @endif
  <div class="kartu">
    <h2>{!! icon('book', 18) !!}{{ $pertemuan->exists ? 'Ubah pertemuan ' . $pertemuan->urutan : 'Pertemuan baru' }}</h2>
    <div class="baris-form">
      <div class="field" style="max-width:120px"><label>Pertemuan ke</label><input type="number" name="urutan" min="1" value="{{ old('urutan', $pertemuan->urutan) }}" required></div>
      <div class="field"><label>Tanggal</label><input type="date" name="tanggal" value="{{ old('tanggal', $pertemuan->tanggal?->format('Y-m-d')) }}" required></div>
    </div>
    <div class="field"><label>Judul / topik</label><input type="text" name="judul" value="{{ old('judul', $pertemuan->judul) }}" required maxlength="160" placeholder="Contoh: Sistem Persamaan Linear Dua Variabel"></div>
    <div class="field"><label>Isi materi</label>
      <textarea name="isi" class="besar" placeholder="Tulis ringkasan materi, langkah belajar, atau petunjuk. Pisahkan paragraf dengan baris kosong. **teks** untuk huruf tebal.">{{ old('isi', $pertemuan->isi) }}</textarea>
      <small>Link (https://...) otomatis bisa diklik siswa.</small></div>
    <div class="field"><label>Link video YouTube (opsional)</label><input type="url" name="video_url" value="{{ old('video_url', $pertemuan->video_url) }}" placeholder="https://www.youtube.com/watch?v=..."></div>
    <div class="field"><label>Lampiran (PDF, Word, PowerPoint, Excel, gambar, ZIP · maks. 20 MB)</label>
      <input type="file" name="lampiran">
      @if ($pertemuan->lampiran_nama)<small>Saat ini: {{ $pertemuan->lampiran_nama }} — <label class="cek" style="display:inline-flex;font-size:12px"><input type="checkbox" name="hapus_lampiran" value="1"> hapus</label></small>@endif
    </div>
  </div>
  <div>
    <div class="kartu">
      <h2>{!! icon('check-square', 18) !!}Presensi daring</h2>
      @php($adaPresensi = old('presensi', $pertemuan->exists ? (bool) $pertemuan->presensi_buka : true))
      <div class="field"><label class="cek"><input type="checkbox" name="presensi" value="1" id="presensi" @checked($adaPresensi)> Buka presensi pada tanggal pertemuan</label></div>
      <div class="baris-form" id="jam-presensi">
        <div class="field"><label>Jam buka</label><input type="time" name="jam_buka" value="{{ old('jam_buka', $pertemuan->presensi_buka?->format('H:i') ?? '07:00') }}"></div>
        <div class="field"><label>Jam tutup</label><input type="time" name="jam_tutup" value="{{ old('jam_tutup', $pertemuan->presensi_tutup?->format('H:i') ?? '15:00') }}"></div>
      </div>
      <small class="kecil-redup">Siswa yang tidak menekan "Saya hadir" sampai jam tutup tercatat alpa. Izin/sakit diisi guru.</small>
    </div>
    <div class="kartu">
      <div class="field"><label class="cek"><input type="checkbox" name="terbit" value="1" @checked(old('terbit', $pertemuan->terbit))> Terbitkan untuk siswa</label><small>Matikan untuk menyimpan sebagai draf.</small></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn">{!! icon('check', 16) !!}Simpan</button>
        <a class="btn garis" href="{{ $pertemuan->exists ? route('pertemuan.show', [$ruang, $pertemuan]) : route('ruang.show', $ruang) }}">Batal</a>
      </div>
    </div>
  </div>
</form>
@if ($pertemuan->exists)
  <div class="mt">@include('partials.hapus', ['url' => route('pertemuan.destroy', [$ruang, $pertemuan]), 'pesan' => 'Hapus pertemuan ini beserta presensinya?', 'kelas' => 'btn bahaya kecil', 'label' => 'Hapus pertemuan'])</div>
@endif
@push('script')
<script>
(function () {
  var c = document.getElementById('presensi'), j = document.getElementById('jam-presensi');
  function atur() { j.style.display = c.checked ? '' : 'none'; }
  c.addEventListener('change', atur); atur();
})();
</script>
@endpush
@endsection
