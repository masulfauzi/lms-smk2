@extends('layouts.app')
@section('title', $kuis->judul)
@section('content')
@include('ruang::_kepala')
@php($st = $kuis->status())
<div class="grid g2">
  <div class="kartu">
    <div class="remah">Kuis · {{ $kuis->soal->count() }} soal · {{ $kuis->durasi_menit }} menit</div>
    <h1 style="font-size:23px;margin-bottom:12px"><span class="sorot">{{ $kuis->judul }}</span></h1>
    @if ($kuis->petunjuk)<div class="materi" style="font-size:14.5px">{!! teks($kuis->petunjuk) !!}</div>@endif
    <table class="mt">
      <tr><td class="kecil-redup">Dibuka</td><td>{{ tgl($kuis->mulai, true) }}</td></tr>
      <tr><td class="kecil-redup">Ditutup</td><td>{{ tgl($kuis->selesai, true) }}</td></tr>
      <tr><td class="kecil-redup">Durasi</td><td>{{ $kuis->durasi_menit }} menit sejak tombol Mulai ditekan</td></tr>
    </table>
  </div>
  <div class="kartu">
    @if ($p && $p->selesai_at)
      <div class="nilai-besar">
        @if ($kuis->tampilkan_nilai && $p->nilai !== null)
          <span>Nilai kamu</span><b>{{ rtrim(rtrim(number_format($p->nilai, 2, ',', ''), '0'), ',') }}</b><span>{{ $p->nilai >= $ruang->kkm ? 'Tuntas' : 'Belum tuntas' }} · KKM {{ $ruang->kkm }}</span>
        @elseif ($p->nilai === null)
          <span>Jawaban terkumpul</span><b style="font-size:30px">Menunggu koreksi</b><span>Soal esai sedang dikoreksi guru.</span>
        @else
          <span>Jawaban terkumpul</span><b style="font-size:30px">Selesai</b><span>Nilai akan diumumkan guru.</span>
        @endif
      </div>
      <p class="kecil-redup" style="text-align:center">Dikumpulkan {{ tgl($p->selesai_at, true) }} · {{ count(array_filter($p->jawaban ?? [], fn ($v) => $v !== null && $v !== '')) }} dari {{ count($p->urutan_soal['soal']) }} soal dijawab</p>
    @elseif ($st === 'berlangsung')
      <h2>{!! icon('quiz', 18) !!}Siap mengerjakan?</h2>
      <ul style="font-size:14px;color:var(--redup);padding-left:18px">
        <li>Waktu {{ $kuis->durasi_menit }} menit mulai berjalan setelah tombol ditekan dan tidak bisa dihentikan.</li>
        <li>Jawaban tersimpan otomatis setiap kamu memilih/mengetik.</li>
        <li>Saat waktu habis, jawaban terkumpul otomatis.</li>
      </ul>
      <form method="post" action="{{ route('kuis.mulai', [$ruang, $kuis]) }}" data-konfirmasi="{{ $p ? '' : 'Mulai mengerjakan sekarang? Waktu langsung berjalan.' }}">
        @csrf<button class="btn stabilo blok">{!! icon('arrow', 16) !!}{{ $p ? 'Lanjutkan mengerjakan' : 'Mulai mengerjakan' }}</button></form>
    @elseif ($st === 'belum')
      @include('partials.kosong', ['ikon' => 'clock', 'judul' => 'Kuis belum dibuka', 'teks' => 'Dibuka ' . tgl($kuis->mulai, true)])
    @else
      @include('partials.kosong', ['ikon' => 'x', 'judul' => 'Kuis sudah ditutup', 'teks' => 'Kamu tidak mengerjakan kuis ini.'])
    @endif
  </div>
</div>
@endsection
