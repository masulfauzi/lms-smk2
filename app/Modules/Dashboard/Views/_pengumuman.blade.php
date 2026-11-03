<div class="kartu">
  <h2>{!! icon('megaphone', 18) !!}Pengumuman <a class="kanan" href="{{ route('pengumuman') }}">Semua</a></h2>
  @forelse ($pengumuman as $p)
    <div style="padding:10px 0;border-bottom:1px dashed var(--garis-2)">
      <div class="tebal" style="display:flex;gap:6px;align-items:center">@if ($p->disematkan){!! icon('pin', 14) !!}@endif{{ $p->judul }}</div>
      <div class="kecil-redup">{{ $p->user->name }} · {{ $p->created_at->locale('id')->diffForHumans() }}</div>
      <div style="font-size:14px;margin-top:4px">{{ \Illuminate\Support\Str::limit($p->isi, 140) }}</div>
    </div>
  @empty
    <p class="kecil-redup">Belum ada pengumuman.</p>
  @endforelse
</div>
