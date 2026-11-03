@extends('layouts.app')
@section('title', 'Anggota · ' . $ruang->judul())
@section('content')
@include('ruang::_kepala')
<div class="grid g-utama">
  <div class="kartu rapat">
    <h2>{!! icon('users', 18) !!}Siswa kelas {{ $ruang->kelas->nama }} <span class="kanan kecil-redup">{{ $siswa->count() }} siswa</span></h2>
    <div class="tabel-wrap mt"><table>
      <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>L/P</th>@if (auth()->user()->role !== 'siswa')<th>Terakhir aktif</th>@endif</tr></thead>
      <tbody>
        @foreach ($siswa as $i => $s)
          <tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $s->name }}</td><td>{{ $s->username }}</td><td>{{ $s->jk ?? '-' }}</td>
            @if (auth()->user()->role !== 'siswa')<td class="kecil-redup">{{ $s->terakhir_aktif_at ? $s->terakhir_aktif_at->locale('id')->diffForHumans() : 'Belum pernah' }}</td>@endif</tr>
        @endforeach
      </tbody>
    </table></div>
  </div>
  <div class="kartu">
    <h2>{!! icon('user', 18) !!}Pengajar</h2>
    <ul class="daftar">
      <li><span class="avatar guru">{{ $ruang->guru?->inisial() ?? '?' }}</span><span class="teks"><b>{{ $ruang->guru?->name ?? 'Belum ditentukan' }}</b><small>Guru {{ $ruang->mapel->nama }}</small></span></li>
      @if ($ruang->kelas->wali)<li><span class="avatar">{{ $ruang->kelas->wali->inisial() }}</span><span class="teks"><b>{{ $ruang->kelas->wali->name }}</b><small>Wali kelas {{ $ruang->kelas->nama }}</small></span></li>@endif
    </ul>
  </div>
</div>
@endsection
