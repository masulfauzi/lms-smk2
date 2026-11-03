@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
@php($u = auth()->user())
<div class="kepala">
  <div><h1>Hai, <span class="sorot">{{ \Illuminate\Support\Str::before($u->name, ' ') }}</span></h1><p>{{ tgl_panjang(now()) }} · Kelas {{ $u->kelas?->nama ?? '-' }}</p></div>
</div>

@foreach ($presensiTerbuka as $p)
  <div class="presensi-panel buka" style="margin-bottom:12px">
    <span class="daftar"><span class="ikon hijau" style="display:grid">{!! icon('check-square', 20) !!}</span></span>
    <div class="teks"><b>Presensi {{ $p->ruang->mapel->nama }} sedang dibuka</b><div class="kecil-redup">Pertemuan {{ $p->urutan }}: {{ $p->judul }} · tutup pukul {{ $p->presensi_tutup->format('H.i') }}</div></div>
    <form method="post" action="{{ route('pertemuan.hadir', [$p->ruang_id, $p]) }}">@csrf<button class="btn hijau">{!! icon('check', 16) !!}Saya hadir</button></form>
  </div>
@endforeach

<div class="grid g-utama">
  <div>
    <div class="kartu">
      <h2>{!! icon('clipboard', 18) !!}Tugas yang harus dikumpulkan</h2>
      @forelse ($tugas as $t)
        @if ($loop->first)<ul class="daftar">@endif
        @php($lewat = $t->lewatTenggat())
        <li><span class="ikon {{ $lewat ? 'merah' : 'kuning' }}">{!! icon('clipboard', 18) !!}</span>
          <span class="teks"><b class="potong">{{ $t->judul }}</b><small>{{ $t->ruang->mapel->nama }} · tenggat {{ tgl($t->tenggat, true) }}</small></span>
          <span class="lencana {{ $lewat ? 'merah' : ($t->tenggat->diffInHours(now(), true) < 24 ? 'oranye' : 'biru') }}">{{ $lewat ? 'Terlambat' : sisa_waktu($t->tenggat) }}</span>
          <a class="btn kecil" href="{{ route('tugas.show', [$t->ruang_id, $t]) }}">Kerjakan</a></li>
        @if ($loop->last)</ul>@endif
      @empty
        @include('partials.kosong', ['ikon' => 'check', 'judul' => 'Tidak ada tugas tertunda', 'teks' => 'Semua tugas sudah kamu kumpulkan.'])
      @endforelse
    </div>
    @if ($kuis->isNotEmpty())
    <div class="kartu">
      <h2>{!! icon('quiz', 18) !!}Kuis &amp; ulangan</h2>
      <ul class="daftar">
        @foreach ($kuis as $k)
          @php($st = $k->status())
          <li><span class="ikon">{!! icon('quiz', 18) !!}</span>
            <span class="teks"><b>{{ $k->judul }}</b><small>{{ $k->ruang->mapel->nama }} · {{ $st === 'berlangsung' ? 'tutup ' . tgl($k->selesai, true) : 'dibuka ' . tgl($k->mulai, true) }} · {{ $k->durasi_menit }} menit</small></span>
            @if ($st === 'berlangsung')<a class="btn kecil stabilo" href="{{ route('kuis.show', [$k->ruang_id, $k]) }}">Kerjakan</a>@else<span class="lencana">Belum dibuka</span>@endif</li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="kartu">
      <h2>{!! icon('book', 18) !!}Mata pelajaran</h2>
      <div class="grid g2">
        @foreach ($ruang as $r)
          <a class="mapel-kartu" href="{{ route('ruang.show', $r) }}">
            <div class="warna" style="background: {{ $r->warna() }}"></div>
            <div class="isi">
              <h3>{{ $r->mapel->nama }}</h3><p>{{ $r->guru?->name ?? '-' }}</p>
              <div class="progres"><i style="width: {{ $progres[$r->id] }}%"></i></div>
              <div class="progres-label"><span>Materi dipelajari</span><b>{{ $progres[$r->id] }}%</b></div>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  </div>
  <div>
    @include('dashboard._jadwal')
    @include('dashboard._pengumuman')
  </div>
</div>
@endsection
