@extends('layouts.app')
@section('title', ($tugas->exists ? 'Ubah' : 'Buat') . ' tugas')
@section('content')
@include('ruang::_kepala')
<form method="post" enctype="multipart/form-data" action="{{ $tugas->exists ? route('tugas.update', [$ruang, $tugas]) : route('tugas.store', $ruang) }}" class="grid g-utama">
  @csrf @if ($tugas->exists) @method('put') @endif
  <div class="kartu">
    <h2>{!! icon('clipboard', 18) !!}{{ $tugas->exists ? 'Ubah tugas' : 'Tugas baru' }}</h2>
    <div class="field"><label>Judul tugas</label><input type="text" name="judul" value="{{ old('judul', $tugas->judul) }}" required maxlength="160" placeholder="Contoh: Latihan Soal SPLDV"></div>
    <div class="field"><label>Instruksi</label><textarea name="instruksi" class="besar" placeholder="Jelaskan apa yang harus dikerjakan, format jawaban, dan kriteria penilaian.">{{ old('instruksi', $tugas->instruksi) }}</textarea></div>
    <div class="field"><label>Lampiran soal (opsional, maks. 20 MB)</label><input type="file" name="lampiran">
      @if ($tugas->lampiran_nama)<small>Saat ini: {{ $tugas->lampiran_nama }}</small>@endif</div>
  </div>
  <div>
    <div class="kartu">
      <div class="field"><label>Tenggat</label><input type="datetime-local" name="tenggat" value="{{ old('tenggat', $tugas->tenggat?->format('Y-m-d\TH:i')) }}" required></div>
      <div class="field"><label>Bentuk pengumpulan</label><select name="jenis_kumpul">
        @foreach (\App\Modules\Tugas\Models\Tugas::JENIS as $v => $l)<option value="{{ $v }}" @selected(old('jenis_kumpul', $tugas->jenis_kumpul) === $v)>{{ $l }}</option>@endforeach</select></div>
      <div class="field"><label>Terkait pertemuan</label><select name="pertemuan_id"><option value="">Tidak terkait</option>
        @foreach ($pertemuan as $p)<option value="{{ $p->id }}" @selected((int) old('pertemuan_id', $tugas->pertemuan_id) === $p->id)>Pertemuan {{ $p->urutan }}: {{ \Illuminate\Support\Str::limit($p->judul, 40) }}</option>@endforeach</select></div>
      <div class="field"><label class="cek"><input type="checkbox" name="boleh_terlambat" value="1" @checked(old('boleh_terlambat', $tugas->boleh_terlambat))> Terima pengumpulan terlambat</label><small>Pengumpulan setelah tenggat diberi tanda "terlambat".</small></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn">{!! icon('check', 16) !!}{{ $tugas->exists ? 'Simpan' : 'Terbitkan tugas' }}</button>
        <a class="btn garis" href="{{ route('tugas.index', $ruang) }}">Batal</a>
      </div>
    </div>
    @if ($tugas->exists)
      @include('partials.hapus', ['url' => route('tugas.destroy', [$ruang, $tugas]), 'pesan' => 'Hapus tugas ini beserta semua pengumpulan siswa?', 'kelas' => 'btn bahaya kecil', 'label' => 'Hapus tugas'])
    @endif
  </div>
</form>
@endsection
