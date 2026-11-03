@extends('layouts.app')
@section('title', $tugas->judul)
@section('content')
@include('partials.ruang-kepala')
<div class="grid g-utama">
  <div class="kartu">@include('tugas._soal')</div>
  <div>
    @if ($milikSaya && $milikSaya->nilai !== null)
      <div class="kartu">
        <div class="nilai-besar"><span>Nilai kamu</span><b>{{ $milikSaya->nilai }}</b><span>{{ $milikSaya->nilai >= $ruang->kkm ? 'Tuntas' : 'Belum tuntas' }} · KKM {{ $ruang->kkm }}</span></div>
        @if ($milikSaya->komentar)<div class="catatan"><div class="tulisan" style="font-size:18px;color:var(--margin)">Komentar guru</div><div style="font-size:14px">{!! teks($milikSaya->komentar) !!}</div></div>@endif
      </div>
    @endif
    <div class="kartu">
      <h2>{!! icon('upload', 18) !!}{{ $milikSaya ? 'Pengumpulan kamu' : 'Kumpulkan tugas' }}</h2>
      @if ($milikSaya)
        <p class="kecil-redup">Dikumpulkan {{ tgl($milikSaya->dikumpulkan_at, true) }} @if ($milikSaya->terlambat)<span class="lencana merah">Terlambat</span>@endif</p>
        @if ($milikSaya->teks)<div class="catatan" style="border-left-color:var(--tinta);margin-bottom:10px;font-size:14px">{!! teks($milikSaya->teks) !!}</div>@endif
        @if ($milikSaya->file_path)<a class="lampiran" href="{{ route('berkas.pengumpulan', [$ruang, $milikSaya]) }}" target="_blank"><span class="ikon">{!! icon('file', 18) !!}</span><b class="potong" style="flex:1">{{ $milikSaya->file_nama }}</b></a>@endif
      @endif
      @if ($milikSaya?->nilai === null)
        @if ($tugas->bisaDikumpulkan())
          @if ($tugas->lewatTenggat())<p class="flash error" style="margin-top:12px">Tenggat sudah lewat. Pengumpulan akan ditandai terlambat.</p>@endif
          <form method="post" enctype="multipart/form-data" action="{{ route('tugas.kumpul', [$ruang, $tugas]) }}" class="mt">
            @csrf
            @if ($tugas->jenis_kumpul !== 'file')
              <div class="field"><label>Jawaban teks</label><textarea name="teks" placeholder="Tulis jawabanmu di sini...">{{ old('teks', $milikSaya?->teks) }}</textarea></div>
            @endif
            @if ($tugas->jenis_kumpul !== 'teks')
              <div class="field"><label>{{ $milikSaya?->file_path ? 'Ganti file' : 'File jawaban' }} (maks. 20 MB)</label><input type="file" name="berkas"><small>PDF, Word, Excel, PowerPoint, foto (JPG/PNG), atau ZIP.</small></div>
            @endif
            <button class="btn blok">{!! icon('upload', 16) !!}{{ $milikSaya ? 'Kumpulkan ulang' : 'Kumpulkan' }}</button>
          </form>
        @else
          <p class="flash error" style="margin-top:12px">Tenggat sudah lewat dan guru tidak menerima pengumpulan terlambat.</p>
        @endif
      @endif
    </div>
  </div>
</div>
@endsection
