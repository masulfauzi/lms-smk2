@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<div class="kepala">
  <div><h1>Halo, <span class="sorot">{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span></h1><p>Ringkasan pembelajaran jarak jauh {{ setting('nama_sekolah') }} hari ini.</p></div>
  <div class="aksi"><a class="btn" href="{{ route('admin.ruang') }}">{!! icon('clipboard', 16) !!}Pembagian mengajar</a></div>
</div>
<div class="grid g4">
  <div class="stat tinta"><small>{!! icon('users', 16) !!}Siswa aktif</small><b>{{ $stat['siswa'] }}</b><div class="sub">{{ $stat['aktif_hari_ini'] }} membuka LMS hari ini</div></div>
  <div class="stat"><small>{!! icon('user', 16) !!}Guru</small><b>{{ $stat['guru'] }}</b></div>
  <div class="stat"><small>{!! icon('grid', 16) !!}Kelas</small><b>{{ $stat['kelas'] }}</b></div>
  <div class="stat"><small>{!! icon('book', 16) !!}Ruang belajar</small><b>{{ $stat['ruang'] }}</b><div class="sub">semester aktif</div></div>
</div>
<div class="grid g-utama mt">
  <div>
    <div class="kartu">
      <h2>{!! icon('check-square', 18) !!}Kehadiran PJJ hari ini <a class="kanan" href="{{ route('admin.rekap.presensi') }}">Rekap bulanan</a></h2>
      @forelse ($perKelas as $k)
        <div class="batang">
          <span class="nama">{{ $k['kelas'] }}</span>
          <span class="trek"><i style="width: {{ $k['persen'] }}%; background: {{ $k['persen'] < 60 ? 'var(--margin)' : 'var(--tinta)' }}"></i></span>
          <b>{{ $k['persen'] }}%</b>
        </div>
      @empty
        @include('partials.kosong', ['ikon' => 'calendar', 'judul' => 'Belum ada pertemuan hari ini', 'teks' => 'Persentase muncul setelah guru membuat pertemuan bertanggal hari ini.'])
      @endforelse
      @if ($perKelas)<p class="kecil-redup">Persen = siswa yang presensi hadir dibanding seluruh siswa x jumlah pertemuan hari ini di kelas tersebut.</p>@endif
    </div>
    <div class="kartu">
      <h2>{!! icon('alert', 18) !!}Ruang belajar tanpa materi 7 hari terakhir</h2>
      @if ($guruBelumIsi->isEmpty())
        <p class="kecil-redup">Semua ruang belajar aktif mengunggah materi minggu ini.</p>
      @else
        <ul class="daftar">
          @foreach ($guruBelumIsi as $r)
            <li><span class="ikon merah">{!! icon('book', 18) !!}</span>
              <span class="teks"><b>{{ $r->mapel->nama }} · {{ $r->kelas->nama }}</b><small>{{ $r->guru?->name ?? 'Belum ada guru' }}</small></span>
              <a class="btn kecil garis" href="{{ route('ruang.show', $r) }}">Lihat</a></li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>
  <div>@include('dashboard._pengumuman')</div>
</div>
@endsection
