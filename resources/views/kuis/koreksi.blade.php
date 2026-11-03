@extends('layouts.app')
@section('title', 'Koreksi · ' . $percobaan->siswa->name)
@section('content')
@include('partials.ruang-kepala')
<div class="kepala">
  <div><div class="remah"><a href="{{ route('kuis.show', [$ruang, $kuis]) }}">{{ $kuis->judul }}</a> / koreksi</div><h1><span class="sorot">{{ $percobaan->siswa->name }}</span></h1>
    <p>Dikumpulkan {{ tgl($percobaan->selesai_at, true) }} · nilai saat ini {{ $percobaan->nilai !== null ? round($percobaan->nilai, 1) : 'menunggu koreksi esai' }}</p></div>
</div>
<form method="post" class="kartu">
  @csrf
  @foreach ($kuis->soal as $s)
    @php($j = $percobaan->jawaban[$s->id] ?? null)
    <div style="padding:16px 0;border-bottom:1px dashed var(--garis-2)">
      <div class="tebal" style="white-space:pre-line">{{ $s->urutan }}. {{ $s->pertanyaan }}</div>
      @if ($s->jenis === 'pg')
        @php($benar = $j === $s->kunci)
        <div style="margin-top:6px;font-size:14px">Jawaban: <b>{{ $j ? $j . '. ' . ($s->opsi[$j] ?? '') : 'Tidak dijawab' }}</b>
          <span class="lencana {{ $benar ? 'hijau' : 'merah' }}">{{ $benar ? 'Benar' : 'Salah · kunci ' . $s->kunci }}</span> <span class="kecil-redup">bobot {{ $s->bobot }}</span></div>
      @else
        <div class="catatan" style="border-left-color:var(--tinta);margin:8px 0;font-size:14.5px">{!! filled($j) ? teks($j) : '<em class="kecil-redup">Tidak dijawab</em>' !!}</div>
        <div class="inline-form"><div class="field"><label>Skor (0–{{ $s->bobot }})</label>
          <input type="number" name="skor[{{ $s->id }}]" min="0" max="{{ $s->bobot }}" step="0.5" value="{{ $percobaan->nilai_esai[$s->id] ?? '' }}" style="max-width:120px" required></div></div>
      @endif
    </div>
  @endforeach
  @if ($kuis->soal->where('jenis', 'esai')->isNotEmpty())
    <button class="btn mt">{!! icon('check', 16) !!}Simpan koreksi</button>
  @endif
</form>
@endsection
