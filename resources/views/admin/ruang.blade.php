@extends('layouts.app')
@section('title', 'Pembagian mengajar')
@section('content')
<div class="kepala">
  <div><h1><span class="sorot">Pembagian mengajar</span></h1><p>Mapel, guru pengampu, dan jadwal kelas daring · {{ $ta?->label() ?? 'tahun ajaran belum aktif' }}</p></div>
  <form class="inline-form" method="get"><div class="field"><select name="kelas" onchange="this.form.submit()">
    @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama }}</option>@endforeach
  </select></div></form>
</div>
@if (!$ta)
  <div class="flash error">Belum ada tahun ajaran aktif. Atur di menu Pengaturan.</div>
@elseif (!$kelas)
  <div class="kartu">@include('partials.kosong', ['ikon' => 'grid', 'judul' => 'Belum ada kelas', 'teks' => 'Tambahkan kelas terlebih dahulu.'])</div>
@else
<div class="grid g-utama">
  <div class="kartu rapat">
    <h2>{!! icon('clipboard', 18) !!}Kelas {{ $kelas->nama }} <span class="kanan kecil-redup">{{ $ruang->count() }} mapel</span></h2>
    <div class="tabel-wrap mt"><table>
      <thead><tr><th>Mapel</th><th>Guru</th><th>Jadwal</th><th></th></tr></thead>
      <tbody>
        @forelse ($ruang as $r)
          <tr>
            <td class="tebal"><a href="{{ route('ruang.show', $r) }}">{{ $r->mapel->nama }}</a></td>
            <td><form method="post" class="inline-form" action="{{ route('admin.ruang') }}">@csrf
              <input type="hidden" name="kelas_id" value="{{ $kelas->id }}"><input type="hidden" name="mapel_id" value="{{ $r->mapel_id }}">
              <div class="field"><select name="guru_id" onchange="this.form.submit()" style="min-width:170px"><option value="">Belum ada guru</option>@foreach ($guru as $g)<option value="{{ $g->id }}" @selected($r->guru_id === $g->id)>{{ $g->name }}</option>@endforeach</select></div></form></td>
            <td style="font-size:13px">
              @foreach ($r->jadwal as $j)
                <div style="display:flex;gap:4px;align-items:center">{{ \App\Models\Jadwal::HARI[$j->hari] }} {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}
                  @if ($j->link_meet){!! icon('video', 13) !!}@endif @include('partials.hapus', ['url' => route('admin.jadwal.hapus', $j), 'pesan' => 'Hapus jadwal ini?'])</div>
              @endforeach
              <details><summary class="kecil-redup" style="cursor:pointer">+ jadwal</summary>
                <form method="post" action="{{ route('admin.ruang.jadwal', $r) }}" class="inline-form" style="margin-top:6px">@csrf
                  <div class="field"><select name="hari">@foreach (\App\Models\Jadwal::HARI as $no => $h)<option value="{{ $no }}">{{ $h }}</option>@endforeach</select></div>
                  <div class="field"><input type="time" name="jam_mulai" value="07:30" required></div>
                  <div class="field"><input type="time" name="jam_selesai" value="09:00" required></div>
                  <div class="field"><input type="url" name="link_meet" placeholder="Link meet (opsional)"></div>
                  <button class="btn kecil">Tambah</button></form></details>
            </td>
            <td class="angka">@include('partials.hapus', ['url' => route('admin.ruang.hapus', $r), 'pesan' => 'Hapus ' . $r->mapel->nama . ' dari kelas ' . $kelas->nama . '?'])</td>
          </tr>
        @empty
          <tr><td colspan="4">@include('partials.kosong', ['ikon' => 'clipboard', 'judul' => 'Belum ada mapel di kelas ini'])</td></tr>
        @endforelse
      </tbody>
    </table></div>
  </div>
  <form method="post" action="{{ route('admin.ruang') }}" class="kartu">
    @csrf <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    <h2>{!! icon('plus', 18) !!}Tambah mapel ke {{ $kelas->nama }}</h2>
    <div class="field"><label>Mata pelajaran</label><select name="mapel_id" required>
      @foreach ($mapel->whereNotIn('id', $ruang->pluck('mapel_id')) as $m)<option value="{{ $m->id }}">{{ $m->nama }}</option>@endforeach</select></div>
    <div class="field"><label>Guru pengampu</label><select name="guru_id"><option value="">Belum ditentukan</option>@foreach ($guru as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
    <button class="btn">{!! icon('plus', 16) !!}Tambah</button>
    <p class="kecil-redup mt">Setiap mapel di satu kelas menjadi satu "ruang belajar" tempat guru mengunggah materi, tugas, dan kuis.</p>
  </form>
</div>
@endif
@endsection
