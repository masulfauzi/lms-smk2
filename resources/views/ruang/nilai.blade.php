@extends('layouts.app')
@section('title', 'Nilai · ' . $ruang->judul())
@section('content')
@php($u = auth()->user())
@include('partials.ruang-kepala')
@if ($u->role === 'siswa')
  @php($b = $rapor['baris'][0] ?? null)
  @if ($b)
  <div class="grid g4">
    <div class="stat tinta"><small>{!! icon('award', 16) !!}Nilai akhir sementara</small><b>{{ $b['akhir'] ?? '-' }}</b><div class="sub">Predikat {{ predikat($b['akhir']) }} · KKM {{ $ruang->kkm }}</div></div>
    <div class="stat"><small>{!! icon('clipboard', 16) !!}Rata-rata tugas</small><b>{{ $b['rata_tugas'] ?? '-' }}</b><div class="sub">bobot {{ $ruang->bobot_tugas }}%</div></div>
    <div class="stat"><small>{!! icon('quiz', 16) !!}Rata-rata kuis</small><b>{{ $b['rata_kuis'] ?? '-' }}</b><div class="sub">bobot {{ $ruang->bobot_kuis }}%</div></div>
    <div class="stat"><small>{!! icon('check-square', 16) !!}Kehadiran</small><b>{{ $b['persen_hadir'] !== null ? $b['persen_hadir'] . '%' : '-' }}</b><div class="sub">{{ $b['hadir'] }} hadir · {{ $b['alpa'] }} alpa</div></div>
  </div>
  <div class="grid g2 mt">
    <div class="kartu rapat"><h2>{!! icon('clipboard', 18) !!}Nilai tugas</h2>
      <div class="tabel-wrap mt"><table><thead><tr><th>Tugas</th><th>Tenggat</th><th class="angka">Nilai</th></tr></thead><tbody>
        @forelse ($rapor['tugas'] as $t)
          @php($n = $b['tugas'][$t->id])
          <tr><td><a href="{{ route('tugas.show', [$ruang, $t]) }}">{{ $t->judul }}</a></td><td class="kecil-redup">{{ tgl($t->tenggat) }}</td>
            <td class="angka tebal {{ $n !== null && $n < $ruang->kkm ? 'n-rendah' : '' }}">{{ $n ?? 'Belum' }}</td></tr>
        @empty<tr><td colspan="3" class="kecil-redup">Belum ada tugas.</td></tr>@endforelse
      </tbody></table></div></div>
    <div class="kartu rapat"><h2>{!! icon('quiz', 18) !!}Nilai kuis</h2>
      <div class="tabel-wrap mt"><table><thead><tr><th>Kuis</th><th>Tanggal</th><th class="angka">Nilai</th></tr></thead><tbody>
        @forelse ($rapor['kuis'] as $k)
          @php($n = $b['kuis'][$k->id])
          <tr><td>{{ $k->judul }}</td><td class="kecil-redup">{{ tgl($k->mulai) }}</td>
            <td class="angka tebal {{ $n !== null && $n < $ruang->kkm ? 'n-rendah' : '' }}">{{ $n === null ? 'Belum' : ($k->tampilkan_nilai ? $n : 'Disembunyikan') }}</td></tr>
        @empty<tr><td colspan="3" class="kecil-redup">Belum ada kuis.</td></tr>@endforelse
      </tbody></table></div></div>
  </div>
  @endif
  <p class="kecil-redup mt">Nilai akhir dihitung dari tugas yang sudah dinilai, kuis yang sudah selesai, dan presensi yang sudah ditutup. Tugas yang tidak dikumpulkan sampai tenggat bernilai 0.</p>
@else
  <div class="kartu rapat">
    <h2>{!! icon('award', 18) !!}Rekap nilai {{ $ruang->kelas->nama }}
      <span class="kanan"><a class="btn kecil garis" href="{{ route('ruang.nilai.csv', $ruang) }}">{!! icon('download', 14) !!}Unduh CSV</a></span></h2>
    <p class="kecil-redup" style="padding:4px 20px 0">Bobot: tugas {{ $ruang->bobot_tugas }}%, kuis {{ $ruang->bobot_kuis }}%, kehadiran {{ $ruang->bobot_kehadiran }}% · KKM {{ $ruang->kkm }}. Ubah di tab Pengaturan.</p>
    <div class="tabel-wrap mt"><table class="matriks">
      <thead><tr><th>No</th><th>Nama</th>
        @foreach ($rapor['tugas'] as $i => $t)<th title="{{ $t->judul }}"><a href="{{ route('tugas.show', [$ruang, $t]) }}">T{{ $i + 1 }}</a></th>@endforeach
        @foreach ($rapor['kuis'] as $i => $k)<th title="{{ $k->judul }}"><a href="{{ route('kuis.show', [$ruang, $k]) }}">K{{ $i + 1 }}</a></th>@endforeach
        <th>Rata T</th><th>Rata K</th><th>% Hadir</th><th>Akhir</th><th>Pred.</th></tr></thead>
      <tbody>
        @foreach ($rapor['baris'] as $i => $b)
          <tr><td>{{ $i + 1 }}</td><td class="tebal" style="white-space:nowrap">{{ $b['siswa']->name }}</td>
            @foreach ($b['tugas'] as $n)<td class="{{ $n !== null && $n < $ruang->kkm ? 'n-rendah' : '' }}">{{ $n ?? '·' }}</td>@endforeach
            @foreach ($b['kuis'] as $n)<td class="{{ $n !== null && $n < $ruang->kkm ? 'n-rendah' : '' }}">{{ $n === null ? '·' : round($n) }}</td>@endforeach
            <td>{{ $b['rata_tugas'] ?? '-' }}</td><td>{{ $b['rata_kuis'] ?? '-' }}</td><td>{{ $b['persen_hadir'] ?? '-' }}</td>
            <td class="tebal {{ $b['akhir'] !== null && $b['akhir'] < $ruang->kkm ? 'n-rendah' : '' }}">{{ $b['akhir'] ?? '-' }}</td><td>{{ predikat($b['akhir']) }}</td></tr>
        @endforeach
        @if (!$rapor['baris'])<tr><td colspan="99" class="kecil-redup">Belum ada siswa di kelas ini.</td></tr>@endif
      </tbody>
    </table></div>
    <p class="kecil-redup" style="padding:12px 20px">Tanda · = belum tenggat / belum dinilai (tidak ikut dihitung). Merah = di bawah KKM.</p>
  </div>
@endif
@endsection
