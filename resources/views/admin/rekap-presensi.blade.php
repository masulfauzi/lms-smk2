@extends('layouts.app')
@section('title', 'Rekap presensi')
@section('content')
<div class="kepala">
  <div><h1><span class="sorot">Rekap presensi</span></h1><p>Gabungan semua mapel kelas {{ $kelas?->nama }} bulan {{ $bulan->locale('id')->translatedFormat('F Y') }} · {{ $pertemuan->count() }} pertemuan dengan presensi.</p></div>
  <div class="aksi">
    <form class="inline-form" method="get">
      <div class="field"><select name="kelas">@foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama }}</option>@endforeach</select></div>
      <div class="field"><input type="month" name="bulan" value="{{ $bulan->format('Y-m') }}"></div>
      <button class="btn garis">Tampilkan</button>
    </form>
    <button class="btn garis" onclick="window.print()">{!! icon('printer', 16) !!}Cetak</button>
  </div>
</div>
<div class="kartu rapat"><div class="tabel-wrap"><table>
  <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th class="angka">Hadir</th><th class="angka">Izin</th><th class="angka">Sakit</th><th class="angka">Alpa</th><th class="angka">% Hadir</th></tr></thead>
  <tbody>
    @forelse ($rekap as $i => $r)
      @php($wajib = $r['hadir'] + $r['alpa'])
      @php($persen = $wajib ? round($r['hadir'] / $wajib * 100) : null)
      <tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $r['siswa']->name }}</td><td>{{ $r['siswa']->username }}</td>
        <td class="angka">{{ $r['hadir'] }}</td><td class="angka">{{ $r['izin'] }}</td><td class="angka">{{ $r['sakit'] }}</td>
        <td class="angka {{ $r['alpa'] >= 3 ? 'n-rendah' : '' }}">{{ $r['alpa'] }}</td><td class="angka tebal {{ $persen !== null && $persen < 75 ? 'n-rendah' : '' }}">{{ $persen !== null ? $persen . '%' : '-' }}</td></tr>
    @empty
      <tr><td colspan="8">@include('partials.kosong', ['ikon' => 'check-square', 'judul' => 'Tidak ada data'])</td></tr>
    @endforelse
  </tbody>
</table></div></div>
<p class="kecil-redup mt">Hanya menghitung pertemuan yang presensinya sudah ditutup. Merah: alpa 3 kali atau lebih / kehadiran di bawah 75%.</p>
@endsection
