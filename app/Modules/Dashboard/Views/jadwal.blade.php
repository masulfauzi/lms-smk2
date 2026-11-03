@extends('layouts.app')
@section('title', 'Jadwal')
@section('content')
<div class="kepala">
  <div><h1><span class="sorot">Jadwal pelajaran</span></h1><p>Jadwal kelas daring per minggu beserta link pertemuan virtual.</p></div>
  @if ($kelasList->isNotEmpty())
    <form class="inline-form" method="get"><div class="field"><select name="kelas" onchange="this.form.submit()">
      @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($k->id === $kelasId)>{{ $k->nama }}</option>@endforeach
    </select></div></form>
  @endif
</div>
<div class="jadwal-hari">
  @foreach (\App\Modules\Ruang\Models\Jadwal::HARI as $no => $hari)
    <div class="kartu" style="{{ $no === now()->dayOfWeekIso ? 'border-color:var(--tinta);border-width:2px' : '' }}">
      <h2>{{ $hari }} @if ($no === now()->dayOfWeekIso)<span class="lencana kuning kanan">Hari ini</span>@endif</h2>
      @forelse ($perHari[$no] ?? [] as $j)
        <div class="slot" style="border-left-color: {{ $j->ruang->warna() }}">
          <b>{{ $j->ruang->mapel->nama }}</b>
          <small>{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }} · {{ auth()->user()->role === 'siswa' ? ($j->ruang->guru?->name ?? '-') : $j->ruang->kelas->nama }}</small>
          @if ($j->link_meet)<div><a class="kecil-redup" href="{{ $j->link_meet }}" target="_blank" rel="noopener" style="display:inline-flex;gap:4px;align-items:center">{!! icon('video', 13) !!}Link kelas daring</a></div>@endif
        </div>
      @empty
        <p class="kecil-redup">Kosong</p>
      @endforelse
    </div>
  @endforeach
</div>
@endsection
