@extends('layouts.app')
@section('title', 'Keaktifan siswa')
@section('content')
<div class="kepala">
  <div><h1><span class="sorot">Keaktifan siswa</span></h1><p>Kapan terakhir siswa kelas {{ $kelas?->nama }} membuka LMS. Gunakan untuk menindaklanjuti siswa yang tidak mengikuti PJJ.</p></div>
  <div class="aksi">@include('rekap::_pilih-kelas')</div>
</div>
@php($lama = $siswa->filter(fn ($s) => !$s->terakhir_aktif_at || $s->terakhir_aktif_at->lt(now()->subDays(3)))->count())
<div class="grid g4">
  <div class="stat tinta"><small>Siswa</small><b>{{ $siswa->count() }}</b></div>
  <div class="stat"><small>Aktif hari ini</small><b>{{ $siswa->filter(fn ($s) => $s->terakhir_aktif_at?->isToday())->count() }}</b></div>
  <div class="stat"><small>Aktif 3 hari terakhir</small><b>{{ $siswa->count() - $lama }}</b></div>
  <div class="stat"><small>Perlu dihubungi</small><b style="color:var(--margin)">{{ $lama }}</b><div class="sub">tidak aktif lebih dari 3 hari</div></div>
</div>
<div class="kartu rapat mt"><div class="tabel-wrap"><table>
  <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Terakhir aktif</th><th>Status</th></tr></thead>
  <tbody>
    @foreach ($siswa as $i => $s)
      @php($t = $s->terakhir_aktif_at)
      <tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $s->name }}</td><td>{{ $s->username }}</td>
        <td class="kecil-redup">{{ $t ? tgl($t, true) . ' (' . $t->locale('id')->diffForHumans() . ')' : 'Belum pernah masuk' }}</td>
        <td>@if (!$t)<span class="lencana merah">Belum pernah</span>@elseif ($t->lt(now()->subDays(3)))<span class="lencana oranye">Perlu dihubungi</span>@elseif ($t->isToday())<span class="lencana hijau">Hari ini</span>@else<span class="lencana biru">Aktif</span>@endif</td></tr>
    @endforeach
  </tbody>
</table></div></div>
@endsection
