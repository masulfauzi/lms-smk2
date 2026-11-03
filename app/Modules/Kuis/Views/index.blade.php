@extends('layouts.app')
@section('title', 'Kuis · ' . $ruang->judul())
@section('content')
@php($u = auth()->user())
@include('ruang::_kepala')
<div class="kartu">
  @forelse ($kuis as $k)
    @if ($loop->first)<ul class="daftar">@endif
    @php($st = $k->status())
    <li>
      <span class="ikon {{ $st === 'berlangsung' ? 'hijau' : '' }}">{!! icon('quiz', 18) !!}</span>
      <span class="teks"><b class="potong"><a href="{{ route($u->role === 'siswa' ? 'kuis.show' : 'kuis.edit', [$ruang, $k]) }}">{{ $k->judul }}</a></b>
        <small>{{ tgl($k->mulai, true) }} – {{ tgl($k->selesai, true) }} · {{ $k->durasi_menit }} menit · {{ $k->soal_count }} soal</small></span>
      <span class="lencana {{ ['draf' => '', 'belum' => 'biru', 'berlangsung' => 'hijau', 'selesai' => ''][$st] }}">{{ ['draf' => 'Draf', 'belum' => 'Belum dibuka', 'berlangsung' => 'Dibuka', 'selesai' => 'Ditutup'][$st] }}</span>
      @if ($u->role === 'siswa')
        @php($p = $milikSaya[$k->id] ?? null)
        @if ($p?->selesai_at)<span class="lencana hijau">{{ $p->nilai !== null && $k->tampilkan_nilai ? 'Nilai ' . round($p->nilai) : 'Selesai' }}</span>@endif
        <a class="btn kecil {{ $st === 'berlangsung' && !$p?->selesai_at ? 'stabilo' : 'garis' }}" href="{{ route('kuis.show', [$ruang, $k]) }}">{{ $st === 'berlangsung' && !$p?->selesai_at ? 'Kerjakan' : 'Lihat' }}</a>
      @else
        <span class="lencana biru">{{ $k->selesai_count }} mengerjakan</span>
        <a class="btn kecil garis" href="{{ route('kuis.show', [$ruang, $k]) }}">Hasil</a>
      @endif
    </li>
    @if ($loop->last)</ul>@endif
  @empty
    @include('partials.kosong', ['ikon' => 'quiz', 'judul' => 'Belum ada kuis'])
  @endforelse
</div>
@endsection
