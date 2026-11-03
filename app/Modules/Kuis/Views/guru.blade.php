@extends('layouts.app')
@section('title', 'Hasil · ' . $kuis->judul)
@section('content')
@include('ruang::_kepala')
@php
  $selesai = $percobaan->whereNotNull('selesai_at');
  $dinilai = $selesai->whereNotNull('nilai');
  $st = $kuis->status();
@endphp
<div class="kepala">
  <div><div class="remah">Kuis · {{ tgl($kuis->mulai, true) }} – {{ tgl($kuis->selesai, true) }}</div><h1><span class="sorot">{{ $kuis->judul }}</span></h1></div>
  <div class="aksi"><a class="btn garis" href="{{ route('kuis.edit', [$ruang, $kuis]) }}">{!! icon('edit', 16) !!}Soal &amp; pengaturan</a></div>
</div>
<div class="grid g4">
  <div class="stat tinta"><small>Status</small><b style="font-size:20px">{{ ['draf' => 'Draf', 'belum' => 'Belum dibuka', 'berlangsung' => 'Sedang dibuka', 'selesai' => 'Ditutup'][$st] }}</b></div>
  <div class="stat"><small>Mengumpulkan</small><b>{{ $selesai->count() }}/{{ $siswa->count() }}</b><div class="sub">{{ $percobaan->count() - $selesai->count() }} sedang mengerjakan</div></div>
  <div class="stat"><small>Rata-rata</small><b>{{ $dinilai->count() ? round($dinilai->avg('nilai'), 1) : '-' }}</b><div class="sub">tertinggi {{ $dinilai->count() ? round($dinilai->max('nilai'), 1) : '-' }} · terendah {{ $dinilai->count() ? round($dinilai->min('nilai'), 1) : '-' }}</div></div>
  <div class="stat"><small>Tuntas (KKM {{ $ruang->kkm }})</small><b>{{ $dinilai->where('nilai', '>=', $ruang->kkm)->count() }}</b><div class="sub">{{ $selesai->whereNull('nilai')->count() }} menunggu koreksi esai</div></div>
</div>
<div class="grid g-utama mt">
  <div class="kartu rapat">
    <h2>{!! icon('users', 18) !!}Hasil per siswa</h2>
    <div class="tabel-wrap mt"><table>
      <thead><tr><th>No</th><th>Nama</th><th>Status</th><th>Waktu</th><th class="angka">Nilai</th><th></th></tr></thead>
      <tbody>
        @foreach ($siswa as $i => $s)
          @php($p = $percobaan[$s->id] ?? null)
          <tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $s->name }}</td>
            <td>@if (!$p)<span class="lencana {{ $st === 'selesai' ? 'merah' : '' }}">{{ $st === 'selesai' ? 'Tidak mengerjakan' : 'Belum' }}</span>
              @elseif (!$p->selesai_at)<span class="lencana biru">Mengerjakan</span>
              @elseif ($p->nilai === null)<span class="lencana kuning">Perlu koreksi</span>
              @else<span class="lencana hijau">Selesai</span>@endif</td>
            <td class="kecil-redup">{{ $p?->selesai_at ? $p->mulai_at->diffInMinutes($p->selesai_at) . ' menit' : '-' }}</td>
            <td class="angka tebal {{ $p && $p->nilai !== null && $p->nilai < $ruang->kkm ? 'n-rendah' : '' }}">{{ $p && $p->nilai !== null ? round($p->nilai, 1) : '-' }}</td>
            <td style="white-space:nowrap">@if ($p?->selesai_at)<a class="btn kecil garis" href="{{ route('kuis.koreksi', [$ruang, $kuis, $p]) }}">{{ $p->nilai === null ? 'Koreksi' : 'Lihat' }}</a>@endif
              @if ($p)@include('partials.hapus', ['url' => route('kuis.reset', [$ruang, $kuis, $p]), 'pesan' => 'Hapus jawaban ' . $s->name . ' agar bisa mengerjakan ulang?'])@endif</td></tr>
        @endforeach
      </tbody>
    </table></div>
  </div>
  <div class="kartu">
    <h2>{!! icon('chart', 18) !!}Analisis butir soal</h2>
    <p class="kecil-redup">Persentase siswa yang menjawab benar. Di bawah 40% = soal sulit / perlu dibahas ulang.</p>
    @forelse ($kuis->soal->where('jenis', 'pg') as $s)
      @php($b = $butir[$s->id])
      <div style="margin-bottom:12px" title="{{ $s->pertanyaan }}">
        <div style="display:flex;justify-content:space-between;font-size:13px"><span class="potong" style="max-width:75%">{{ $s->urutan }}. {{ \Illuminate\Support\Str::limit($s->pertanyaan, 50) }}</span>
          <b class="{{ $b['persen'] !== null && $b['persen'] < 40 ? 'n-rendah' : '' }}">{{ $b['persen'] ?? '-' }}{{ $b['persen'] !== null ? '%' : '' }}</b></div>
        <div class="butir"><i style="width: {{ $b['persen'] ?? 0 }}%; background: {{ ($b['persen'] ?? 0) < 40 ? 'var(--margin)' : 'var(--hijau)' }}"></i></div>
        @if ($b['sebaran']->isNotEmpty())<div class="kecil-redup" style="margin-top:2px">@foreach ($b['sebaran']->sortKeys() as $h => $n){{ $h }}: {{ $n }}{{ $h === $s->kunci ? ' (kunci)' : '' }}@if (!$loop->last) · @endif @endforeach</div>@endif
      </div>
    @empty
      <p class="kecil-redup">Tidak ada soal pilihan ganda.</p>
    @endforelse
  </div>
</div>
@endsection
