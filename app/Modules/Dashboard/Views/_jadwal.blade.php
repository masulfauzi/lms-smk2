<div class="kartu">
  <h2>{!! icon('calendar', 18) !!}Jadwal hari ini <span class="kanan kecil-redup">{{ tgl_panjang(now()) }}</span></h2>
  @forelse ($jadwal as $j)
    @php($jalan = now()->format('H:i:s') >= $j->jam_mulai && now()->format('H:i:s') <= $j->jam_selesai)
    <div class="slot" style="border-left-color: {{ $j->ruang->warna() }}">
      <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
        <b>{{ $j->ruang->mapel->nama }}</b>
        @if ($jalan)<span class="lencana hijau">Berlangsung</span>@endif
      </div>
      <small>{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }} · {{ auth()->user()->role === 'siswa' ? ($j->ruang->guru?->name ?? '-') : $j->ruang->kelas->nama }}</small>
      <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
        <a class="btn kecil garis" href="{{ route('ruang.show', $j->ruang) }}">{!! icon('book', 14) !!}Buka ruang</a>
        @if ($j->link_meet)<a class="btn kecil {{ $jalan ? 'stabilo' : 'garis' }}" href="{{ $j->link_meet }}" target="_blank" rel="noopener">{!! icon('video', 14) !!}Kelas daring</a>@endif
      </div>
    </div>
  @empty
    <p class="kecil-redup">Tidak ada jadwal hari ini.</p>
  @endforelse
</div>
