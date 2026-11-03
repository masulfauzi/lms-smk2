@extends('layouts.app')
@section('title', 'Presensi · ' . $ruang->judul())
@section('content')
@include('partials.ruang-kepala')
<div class="kartu rapat">
  <h2>{!! icon('check-square', 18) !!}Rekap presensi <span class="kanan" style="display:flex;gap:6px">
    <span class="sel h">H</span><span class="kecil-redup">hadir</span><span class="sel i">I</span><span class="kecil-redup">izin</span><span class="sel s">S</span><span class="kecil-redup">sakit</span><span class="sel a">A</span><span class="kecil-redup">alpa</span></span></h2>
  @if ($pertemuan->isEmpty())
    @include('partials.kosong', ['ikon' => 'check-square', 'judul' => 'Belum ada pertemuan dengan presensi'])
  @else
  <div class="tabel-wrap mt"><table class="matriks">
    <thead><tr><th>No</th><th>Nama</th>
      @foreach ($pertemuan as $p)<th title="{{ $p->judul }}"><a href="{{ route('pertemuan.show', [$ruang, $p]) }}">P{{ $p->urutan }}</a><div class="kecil-redup" style="font-weight:400;text-transform:none">{{ $p->tanggal->format('d/m') }}</div></th>@endforeach
      <th>H</th><th>I/S</th><th>A</th><th>%</th></tr></thead>
    <tbody>
      @foreach ($siswa as $i => $s)
        @php
          $sel = [];
          $h = $is = $a = 0;
          foreach ($pertemuan as $p) {
              $st = $data[$s->id][$p->id]->status ?? null;
              if (!$st && $p->presensi_tutup && $p->presensi_tutup->isPast()) $st = 'alpa';
              if ($st === 'hadir') $h++;
              elseif ($st === 'izin' || $st === 'sakit') $is++;
              elseif ($st === 'alpa') $a++;
              $sel[$p->id] = $st;
          }
          $wajib = $h + $a;
        @endphp
        <tr><td>{{ $i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ $s->name }}</td>
          @foreach ($pertemuan as $p)
            @php($st = $sel[$p->id])
            <td>@if ($st)<span class="sel {{ substr($st, 0, 1) }}" title="{{ \App\Models\Presensi::STATUS[$st] }}">{{ strtoupper(substr($st, 0, 1)) }}</span>@else<span class="sel k">·</span>@endif</td>
          @endforeach
          <td class="tebal">{{ $h }}</td><td>{{ $is }}</td><td class="{{ $a ? 'n-rendah' : '' }}">{{ $a }}</td><td>{{ $wajib ? round($h / $wajib * 100) : '-' }}</td></tr>
      @endforeach
    </tbody>
  </table></div>
  <p class="kecil-redup" style="padding:12px 20px">Siswa yang tidak presensi sampai jam tutup otomatis tercatat alpa. Guru bisa mengubah status (izin/sakit) di halaman tiap pertemuan.</p>
  @endif
</div>
@endsection
