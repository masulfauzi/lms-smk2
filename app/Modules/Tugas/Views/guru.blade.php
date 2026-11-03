@extends('layouts.app')
@section('title', $tugas->judul)
@section('content')
@include('ruang::_kepala')
<div class="grid g-utama">
  <div>
    <div class="kartu">
      @include('tugas::_soal')
      <div style="margin-top:14px"><a class="btn garis kecil" href="{{ route('tugas.edit', [$ruang, $tugas]) }}">{!! icon('edit', 14) !!}Ubah tugas</a></div>
    </div>
    @if ($pilih)
      <div class="kartu" id="nilai">
        <h2>{!! icon('file', 18) !!}Jawaban {{ $pilih->siswa->name }}</h2>
        <p class="kecil-redup">Dikumpulkan {{ tgl($pilih->dikumpulkan_at, true) }} @if ($pilih->terlambat)<span class="lencana merah">Terlambat</span>@endif</p>
        @if ($pilih->teks)<div class="catatan" style="border-left-color:var(--tinta);margin-bottom:10px;font-size:14.5px">{!! teks($pilih->teks) !!}</div>@endif
        @if ($pilih->file_path)<a class="lampiran" href="{{ route('berkas.pengumpulan', [$ruang, $pilih]) }}" target="_blank"><span class="ikon">{!! icon('file', 18) !!}</span><b class="potong" style="flex:1">{{ $pilih->file_nama }}</b>{!! icon('eye', 18) !!}</a>@endif
        <form method="post" action="{{ route('tugas.nilai', [$ruang, $tugas, $pilih]) }}" class="mt">
          @csrf
          <div class="baris-form">
            <div class="field" style="max-width:140px"><label>Nilai (0-100)</label><input type="number" name="nilai" min="0" max="100" value="{{ $pilih->nilai }}" required autofocus></div>
          </div>
          <div class="field"><label>Komentar untuk siswa (opsional)</label><textarea name="komentar" style="min-height:80px">{{ $pilih->komentar }}</textarea></div>
          <button class="btn">{!! icon('check', 16) !!}Simpan nilai &amp; lanjut</button>
        </form>
      </div>
    @endif
  </div>
  <div class="kartu rapat">
    <h2>{!! icon('users', 18) !!}Pengumpulan <span class="kanan kecil-redup">{{ $kumpul->count() }}/{{ $siswa->count() }}</span></h2>
    <ul class="daftar" style="padding:6px 20px 12px">
      @foreach ($siswa as $s)
        @php($k = $kumpul[$s->id] ?? null)
        <li style="{{ $pilih && $pilih->siswa_id === $s->id ? 'background:var(--stabilo-lembut);margin:0 -20px;padding-left:20px;padding-right:20px' : '' }}">
          <span class="teks"><b class="potong">@if ($k)<a href="?siswa={{ $s->id }}#nilai">{{ $s->name }}</a>@else{{ $s->name }}@endif</b>
            <small>{{ $k ? tgl($k->dikumpulkan_at, true) : 'Belum mengumpulkan' }}</small></span>
          @if ($k && $k->nilai !== null)<span class="lencana hijau">{{ $k->nilai }}</span>
          @elseif ($k)<span class="lencana {{ $k->terlambat ? 'oranye' : 'kuning' }}">{{ $k->terlambat ? 'Terlambat' : 'Belum dinilai' }}</span>
          @elseif ($tugas->lewatTenggat())<span class="lencana merah">Tidak ada</span>
          @else<span class="lencana">-</span>@endif
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endsection
