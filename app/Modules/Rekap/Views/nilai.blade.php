@extends('layouts.app')
@section('title', 'Leger nilai')
@section('content')
<div class="kepala">
  <div><h1><span class="sorot">Leger nilai</span></h1><p>Nilai akhir sementara semua mapel kelas {{ $kelas?->nama }} · {{ \App\Modules\Sekolah\Models\TahunAjaran::aktif()?->label() }}</p></div>
  <div class="aksi">
    @include('rekap::_pilih-kelas')
    @if ($kelas)<a class="btn garis" href="{{ route('admin.rekap.nilai', ['kelas' => $kelas->id, 'csv' => 1]) }}">{!! icon('download', 16) !!}CSV</a>@endif
    <button class="btn garis" onclick="window.print()">{!! icon('printer', 16) !!}Cetak</button>
  </div>
</div>
<div class="kartu rapat"><div class="tabel-wrap"><table class="matriks">
  <thead><tr><th>No</th><th>Nama</th>@foreach ($ruang as $r)<th title="{{ $r->mapel->nama }}"><a href="{{ route('ruang.nilai', $r) }}">{{ $r->mapel->kode }}</a></th>@endforeach<th>Rata-rata</th><th>Rank</th></tr></thead>
  <tbody>
    @php
      $rata = [];
      foreach ($siswa as $s) {
          $ada = array_filter($nilai[$s->id] ?? [], fn ($n) => $n !== null);
          $rata[$s->id] = $ada ? round(array_sum($ada) / count($ada), 1) : null;
      }
      $urut = collect($rata)->filter(fn ($n) => $n !== null)->sortDesc()->keys()->flip();
    @endphp
    @forelse ($siswa as $i => $s)
      <tr><td>{{ $i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ $s->name }}</td>
        @foreach ($ruang as $r)
          @php($n = $nilai[$s->id][$r->id] ?? null)
          <td class="{{ $n !== null && $n < $r->kkm ? 'n-rendah' : '' }}">{{ $n ?? '·' }}</td>
        @endforeach
        <td class="tebal">{{ $rata[$s->id] ?? '-' }}</td><td>{{ isset($urut[$s->id]) ? $urut[$s->id] + 1 : '-' }}</td></tr>
    @empty
      <tr><td colspan="99">@include('partials.kosong', ['ikon' => 'award', 'judul' => 'Tidak ada data'])</td></tr>
    @endforelse
  </tbody>
</table></div></div>
<p class="kecil-redup mt">Kolom memakai kode mapel. Merah = di bawah KKM mapel. Nilai bersifat sementara dan berubah seiring tugas/kuis baru.</p>
@endsection
