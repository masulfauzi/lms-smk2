@extends('layouts.app')
@section('title', 'Mata pelajaran')
@section('content')
<div class="kepala"><div><h1><span class="sorot">Mata pelajaran</span></h1><p>Daftar mapel yang bisa dibagikan ke kelas.</p></div></div>
<div class="grid g-utama">
  <div class="kartu rapat"><div class="tabel-wrap"><table>
    <thead><tr><th>Kode</th><th>Mata pelajaran</th><th>Kelompok</th><th class="angka">Dipakai</th><th></th></tr></thead>
    <tbody>
      @forelse ($daftar as $m)
        <tr><td class="tebal">{{ $m->kode }}</td><td>{{ $m->nama }}</td><td><span class="lencana {{ $m->kelompok === 'Kejuruan' ? 'kuning' : ($m->kelompok === 'Umum' ? 'biru' : '') }}">{{ $m->kelompok }}</span></td>
          <td class="angka">{{ $dipakai[$m->id] ?? 0 }} kelas</td>
          <td class="angka" style="white-space:nowrap"><a class="ikon-btn" href="?ubah={{ $m->id }}">{!! icon('edit', 16) !!}</a>@include('partials.hapus', ['url' => route('admin.mapel.hapus', $m)])</td></tr>
      @empty<tr><td colspan="5">@include('partials.kosong', ['ikon' => 'layers', 'judul' => 'Belum ada mata pelajaran'])</td></tr>@endforelse
    </tbody></table></div></div>
  <form method="post" class="kartu">
    @csrf <input type="hidden" name="id" value="{{ $ubah?->id }}">
    <h2>{!! icon($ubah ? 'edit' : 'plus', 18) !!}{{ $ubah ? 'Ubah mapel' : 'Tambah mapel' }}</h2>
    <div class="field"><label>Kode</label><input type="text" name="kode" value="{{ old('kode', $ubah?->kode) }}" placeholder="MTK" required maxlength="15"></div>
    <div class="field"><label>Nama</label><input type="text" name="nama" value="{{ old('nama', $ubah?->nama) }}" placeholder="Matematika" required></div>
    <div class="field"><label>Kelompok</label><select name="kelompok">@foreach (['Umum', 'Kejuruan', 'Muatan Lokal'] as $k)<option @selected(old('kelompok', $ubah?->kelompok) === $k)>{{ $k }}</option>@endforeach</select></div>
    <button class="btn">{!! icon('check', 16) !!}Simpan</button> @if ($ubah)<a class="btn garis" href="{{ route('admin.mapel') }}">Batal</a>@endif
  </form>
</div>
@endsection
