@extends('layouts.app')
@section('title', 'Ruang Belajar')
@section('content')
@php($u = auth()->user())
<div class="kepala">
  <div><h1><span class="sorot">Ruang belajar</span></h1><p>{{ $u->role === 'siswa' ? 'Mata pelajaran kelas ' . ($u->kelas?->nama ?? '-') : ($u->role === 'guru' ? 'Mapel dan kelas yang Anda ampu.' : 'Seluruh ruang belajar semester aktif.') }}</p></div>
  @if ($u->role === 'admin')
    <form class="inline-form" method="get"><div class="field"><select name="kelas" onchange="this.form.submit()"><option value="">Semua kelas</option>
      @foreach (\App\Models\Kelas::orderBy('tingkat')->orderBy('nama')->get() as $k)<option value="{{ $k->id }}" @selected(request('kelas') == $k->id)>{{ $k->nama }}</option>@endforeach
    </select></div></form>
  @endif
</div>
@if ($ruang->isEmpty())
  <div class="kartu">@include('partials.kosong', ['judul' => 'Belum ada ruang belajar', 'teks' => $u->role === 'admin' ? 'Atur di menu Pembagian Mengajar.' : 'Hubungi admin sekolah.'])</div>
@else
  <div class="grid g3">
    @foreach ($ruang as $r)
      <a class="mapel-kartu" href="{{ route('ruang.show', $r) }}">
        <div class="warna" style="background: {{ $r->warna() }}"></div>
        <div class="isi">
          <h3>{{ $r->mapel->nama }}</h3>
          <p>{{ $u->role === 'siswa' ? ($r->guru?->name ?? '-') : $r->kelas->nama . ' · ' . ($r->guru?->name ?? 'Belum ada guru') }}</p>
          <span class="lencana biru">{!! icon('book', 12) !!}{{ $r->pertemuan_count }} pertemuan</span>
        </div>
      </a>
    @endforeach
  </div>
@endif
@endsection
