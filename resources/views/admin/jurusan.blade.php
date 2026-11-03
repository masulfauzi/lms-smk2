@extends('layouts.app')
@section('title', 'Jurusan')
@section('content')
<div class="kepala"><div><h1><span class="sorot">Jurusan</span></h1><p>Program keahlian (SMK) atau peminatan (SMA).</p></div></div>
<div class="grid g-utama">
  <div class="kartu rapat"><div class="tabel-wrap"><table>
    <thead><tr><th>Kode</th><th>Nama jurusan</th><th class="angka">Kelas</th><th></th></tr></thead>
    <tbody>
      @forelse ($daftar as $j)
        <tr><td class="tebal">{{ $j->kode }}</td><td>{{ $j->nama }}</td><td class="angka">{{ $j->kelas_count }}</td>
          <td class="angka" style="white-space:nowrap"><a class="ikon-btn" href="?ubah={{ $j->id }}">{!! icon('edit', 16) !!}</a>@include('partials.hapus', ['url' => route('admin.jurusan.hapus', $j)])</td></tr>
      @empty<tr><td colspan="4">@include('partials.kosong', ['ikon' => 'flag', 'judul' => 'Belum ada jurusan'])</td></tr>@endforelse
    </tbody></table></div></div>
  <form method="post" class="kartu">
    @csrf <input type="hidden" name="id" value="{{ $ubah?->id }}">
    <h2>{!! icon($ubah ? 'edit' : 'plus', 18) !!}{{ $ubah ? 'Ubah jurusan' : 'Tambah jurusan' }}</h2>
    <div class="field"><label>Kode</label><input type="text" name="kode" value="{{ old('kode', $ubah?->kode) }}" placeholder="TKJ" required maxlength="15"></div>
    <div class="field"><label>Nama</label><input type="text" name="nama" value="{{ old('nama', $ubah?->nama) }}" placeholder="Teknik Komputer dan Jaringan" required></div>
    <button class="btn">{!! icon('check', 16) !!}Simpan</button> @if ($ubah)<a class="btn garis" href="{{ route('admin.jurusan') }}">Batal</a>@endif
  </form>
</div>
@endsection
