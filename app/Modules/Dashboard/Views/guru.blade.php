@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<div class="kepala">
  <div><h1>Selamat mengajar, <span class="sorot">{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span></h1><p>{{ tgl_panjang(now()) }}</p></div>
</div>
<div class="grid g4">
  <div class="stat tinta"><small>{!! icon('book', 16) !!}Ruang belajar</small><b>{{ $ruang->count() }}</b><div class="sub">semester ini</div></div>
  <div class="stat"><small>{!! icon('clipboard', 16) !!}Perlu dinilai</small><b>{{ $jumlahPerluDinilai }}</b><div class="sub">pengumpulan tugas</div></div>
  <div class="stat"><small>{!! icon('quiz', 16) !!}Kuis aktif</small><b>{{ $kuisAktif->count() }}</b></div>
  <div class="stat"><small>{!! icon('calendar', 16) !!}Jadwal hari ini</small><b>{{ $jadwal->count() }}</b></div>
</div>
<div class="grid g-utama mt">
  <div>
    <div class="kartu">
      <h2>{!! icon('clipboard', 18) !!}Tugas menunggu penilaian</h2>
      @forelse ($perluDinilai as $p)
        @if ($loop->first)<ul class="daftar">@endif
        <li><span class="ikon kuning">{!! icon('file', 18) !!}</span>
          <span class="teks"><b class="potong">{{ $p->siswa->name }} · {{ $p->tugas->judul }}</b>
            <small>{{ $p->tugas->ruang->mapel->nama }} {{ $p->tugas->ruang->kelas->nama }} · dikumpulkan {{ $p->dikumpulkan_at->locale('id')->diffForHumans() }}@if ($p->terlambat) · <span style="color:var(--merah)">terlambat</span>@endif</small></span>
          <a class="btn kecil" href="{{ route('tugas.show', [$p->tugas->ruang_id, $p->tugas_id]) }}?siswa={{ $p->siswa_id }}">Nilai</a></li>
        @if ($loop->last)</ul>@endif
      @empty
        @include('partials.kosong', ['ikon' => 'check', 'judul' => 'Semua tugas sudah dinilai'])
      @endforelse
    </div>
    <div class="kartu">
      <h2>{!! icon('book', 18) !!}Ruang belajar saya</h2>
      <div class="grid g2">
        @foreach ($ruang as $r)
          <a class="mapel-kartu" href="{{ route('ruang.show', $r) }}">
            <div class="warna" style="background: {{ $r->warna() }}"></div>
            <div class="isi"><h3>{{ $r->mapel->nama }}</h3><p>{{ $r->kelas->nama }} · {{ $r->pertemuan_count }} pertemuan</p></div>
          </a>
        @endforeach
      </div>
      @if ($ruang->isEmpty())@include('partials.kosong', ['judul' => 'Belum ada ruang belajar', 'teks' => 'Admin sekolah perlu mengatur pembagian mengajar.'])@endif
    </div>
  </div>
  <div>
    @include('dashboard::_jadwal')
    @if ($kuisAktif->isNotEmpty())
    <div class="kartu">
      <h2>{!! icon('quiz', 18) !!}Kuis aktif</h2>
      <ul class="daftar">
        @foreach ($kuisAktif as $k)
          <li><span class="teks"><b>{{ $k->judul }}</b><small>{{ $k->ruang->mapel->nama }} {{ $k->ruang->kelas->nama }} · {{ $k->percobaan_count }} selesai · tutup {{ tgl($k->selesai, true) }}</small></span>
            <a class="btn kecil garis" href="{{ route('kuis.show', [$k->ruang_id, $k]) }}">Hasil</a></li>
        @endforeach
      </ul>
    </div>
    @endif
    @include('dashboard::_pengumuman')
  </div>
</div>
@endsection
