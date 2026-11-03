@extends('layouts.app')
@section('title', 'Pengaturan · ' . $ruang->judul())
@section('content')
@include('ruang::_kepala')
<form method="post" class="grid g2">
  @csrf
  <div class="kartu">
    <h2>{!! icon('settings', 18) !!}Ruang belajar</h2>
    <div class="field"><label>Catatan / deskripsi untuk siswa</label><textarea name="deskripsi" maxlength="2000" placeholder="Misal: aturan pengumpulan tugas, buku pegangan, kontak guru.">{{ old('deskripsi', $ruang->deskripsi) }}</textarea></div>
    <div class="field"><label>KKM</label><input type="number" name="kkm" min="0" max="100" value="{{ old('kkm', $ruang->kkm) }}" required></div>
    <label>Bobot nilai akhir (total harus 100%)</label>
    <div class="baris-form">
      <div class="field"><small>Tugas %</small><input type="number" name="bobot_tugas" min="0" max="100" value="{{ old('bobot_tugas', $ruang->bobot_tugas) }}" required></div>
      <div class="field"><small>Kuis %</small><input type="number" name="bobot_kuis" min="0" max="100" value="{{ old('bobot_kuis', $ruang->bobot_kuis) }}" required></div>
      <div class="field"><small>Kehadiran %</small><input type="number" name="bobot_kehadiran" min="0" max="100" value="{{ old('bobot_kehadiran', $ruang->bobot_kehadiran) }}" required></div>
    </div>
  </div>
  <div class="kartu">
    <h2>{!! icon('video', 18) !!}Link kelas daring per jadwal</h2>
    @forelse ($ruang->jadwal as $j)
      <div class="field"><label>{{ \App\Modules\Ruang\Models\Jadwal::HARI[$j->hari] }}, {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</label>
        <input type="url" name="link[{{ $j->id }}]" value="{{ old('link.' . $j->id, $j->link_meet) }}" placeholder="https://meet.google.com/..."></div>
    @empty
      <p class="kecil-redup">Jadwal ruang belajar ini belum diatur admin sekolah.</p>
    @endforelse
    <button class="btn">{!! icon('check', 16) !!}Simpan pengaturan</button>
  </div>
</form>
@endsection
