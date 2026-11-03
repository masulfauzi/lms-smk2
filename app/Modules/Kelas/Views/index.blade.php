@extends('layouts.app')
@section('title', 'Kelas')
@section('content')
@php($pakaiJurusan = in_array(setting('jenjang'), ['SMA', 'SMK']))
<div class="kepala"><div><h1><span class="sorot">Kelas</span></h1><p>Rombongan belajar jenjang {{ setting('jenjang') }} beserta wali kelasnya.</p></div></div>
<div class="grid g-utama">
  <div class="kartu rapat"><div class="tabel-wrap"><table>
    <thead><tr><th>Kelas</th><th>Tingkat</th>@if ($pakaiJurusan)<th>Jurusan</th>@endif<th>Wali kelas</th><th class="angka">Siswa</th><th class="angka">Mapel</th><th></th></tr></thead>
    <tbody>
      @forelse ($daftar as $k)
        <tr><td class="tebal"><a href="{{ route('admin.pengguna', ['siswa', 'kelas' => $k->id]) }}">{{ $k->nama }}</a></td><td>{{ $k->tingkat }}</td>
          @if ($pakaiJurusan)<td>{{ $k->jurusan?->kode ?? '-' }}</td>@endif
          <td>{{ $k->wali?->name ?? '-' }}</td><td class="angka">{{ $k->siswa_count }}</td><td class="angka"><a href="{{ route('admin.ruang', ['kelas' => $k->id]) }}">{{ $k->ruang_count }}</a></td>
          <td class="angka" style="white-space:nowrap"><a class="ikon-btn" href="?ubah={{ $k->id }}">{!! icon('edit', 16) !!}</a>@include('partials.hapus', ['url' => route('admin.kelas.hapus', $k)])</td></tr>
      @empty<tr><td colspan="7">@include('partials.kosong', ['ikon' => 'grid', 'judul' => 'Belum ada kelas'])</td></tr>@endforelse
    </tbody></table></div></div>
  <form method="post" class="kartu">
    @csrf <input type="hidden" name="id" value="{{ $ubah?->id }}">
    <h2>{!! icon($ubah ? 'edit' : 'plus', 18) !!}{{ $ubah ? 'Ubah kelas' : 'Tambah kelas' }}</h2>
    <div class="baris-form">
      <div class="field"><label>Tingkat</label><select name="tingkat">@foreach (\App\Modules\Kelas\Models\Kelas::tingkatJenjang() as $t)<option @selected((int) old('tingkat', $ubah?->tingkat) === $t)>{{ $t }}</option>@endforeach</select></div>
      @if ($pakaiJurusan)
        <div class="field"><label>Jurusan</label><select name="jurusan_id"><option value="">-</option>@foreach ($jurusan as $j)<option value="{{ $j->id }}" @selected((int) old('jurusan_id', $ubah?->jurusan_id) === $j->id)>{{ $j->kode }}</option>@endforeach</select></div>
      @endif
    </div>
    <div class="field"><label>Nama kelas</label><input type="text" name="nama" value="{{ old('nama', $ubah?->nama) }}" placeholder="{{ setting('jenjang') === 'SD' ? '4A' : (setting('jenjang') === 'SMP' ? 'VIII B' : 'X TKJ 1') }}" required maxlength="40"></div>
    <div class="field"><label>Wali kelas</label><select name="wali_id"><option value="">-</option>@foreach ($guru as $g)<option value="{{ $g->id }}" @selected((int) old('wali_id', $ubah?->wali_id) === $g->id)>{{ $g->name }}</option>@endforeach</select></div>
    <button class="btn">{!! icon('check', 16) !!}Simpan</button> @if ($ubah)<a class="btn garis" href="{{ route('admin.kelas') }}">Batal</a>@endif
  </form>
</div>
@endsection
