<div class="remah">Tugas · tenggat {{ tgl_panjang($tugas->tenggat) }} pukul {{ $tugas->tenggat->format('H.i') }}</div>
<h1 style="font-size:23px;margin-bottom:14px"><span class="sorot">{{ $tugas->judul }}</span></h1>
<div class="materi">{!! $tugas->instruksi ? teks($tugas->instruksi) : '<p class="kecil-redup">Tidak ada instruksi tertulis.</p>' !!}</div>
@if ($tugas->lampiran_path)
  <a class="lampiran" href="{{ route('berkas.tugas', [$ruang, $tugas]) }}" target="_blank"><span class="ikon">{!! icon('file', 20) !!}</span>
    <span style="flex:1;min-width:0"><b class="potong" style="display:block">{{ $tugas->lampiran_nama }}</b><small class="kecil-redup">Lampiran soal</small></span>{!! icon('download', 18) !!}</a>
@endif
