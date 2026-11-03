@extends('layouts.app')
@section('title', 'Diskusi · ' . $ruang->judul())
@section('content')
@php($u = auth()->user())
@include('ruang::_kepala')
<div class="grid g-utama">
  <div class="kartu">
    <form method="post" action="{{ route('ruang.diskusi', $ruang) }}">
      @csrf
      <div class="field"><label>Tulis pertanyaan atau bahan diskusi</label><textarea name="isi" required maxlength="3000" style="min-height:80px" placeholder="Contoh: Pak/Bu, maksud soal nomor 3 bagaimana?"></textarea></div>
      <button class="btn">{!! icon('message', 16) !!}Kirim</button>
    </form>
    <div class="mt2">
      @forelse ($topik as $d)
        <div class="pesan">
          <span class="avatar kecil {{ $d->user->role !== 'siswa' ? 'guru' : '' }}">{{ $d->user->inisial() }}</span>
          <div class="isi">
            <div class="nama">{{ $d->user->name }} @if ($d->user->role === 'guru')<span class="lencana kuning">Guru</span>@endif <small>{{ $d->created_at->locale('id')->diffForHumans() }}</small>
              @if ($d->user_id === $u->id || $u->role !== 'siswa')<span style="margin-left:auto">@include('partials.hapus', ['url' => route('diskusi.hapus', [$ruang, $d]), 'pesan' => 'Hapus pesan ini beserta balasannya?'])</span>@endif</div>
            <div style="font-size:14.5px;margin-top:4px">{!! teks($d->isi) !!}</div>
            <div class="balas">
              @foreach ($d->balasan as $b)
                <div class="pesan">
                  <span class="avatar kecil {{ $b->user->role !== 'siswa' ? 'guru' : '' }}">{{ $b->user->inisial() }}</span>
                  <div class="isi"><div class="nama">{{ $b->user->name }} @if ($b->user->role === 'guru')<span class="lencana kuning">Guru</span>@endif <small>{{ $b->created_at->locale('id')->diffForHumans() }}</small>
                    @if ($b->user_id === $u->id || $u->role !== 'siswa')<span style="margin-left:auto">@include('partials.hapus', ['url' => route('diskusi.hapus', [$ruang, $b]), 'pesan' => 'Hapus balasan ini?'])</span>@endif</div>
                    <div style="font-size:14px;margin-top:2px">{!! teks($b->isi) !!}</div></div>
                </div>
              @endforeach
              <form method="post" action="{{ route('ruang.diskusi', $ruang) }}" class="inline-form" style="margin-top:8px">
                @csrf <input type="hidden" name="parent_id" value="{{ $d->id }}">
                <div class="field" style="flex:1;min-width:180px"><input type="text" name="isi" required maxlength="3000" placeholder="Tulis balasan..."></div>
                <button class="btn kecil garis">Balas</button>
              </form>
            </div>
          </div>
        </div>
      @empty
        @include('partials.kosong', ['ikon' => 'message', 'judul' => 'Belum ada diskusi', 'teks' => 'Jadilah yang pertama bertanya.'])
      @endforelse
      {{ $topik->links() }}
    </div>
  </div>
  <div class="catatan"><div class="tulisan" style="font-size:19px;color:var(--margin)">Etika diskusi</div>
    <p style="font-size:14px">Gunakan bahasa yang sopan, tanyakan hal yang berkaitan dengan pelajaran, dan saling bantu menjawab pertanyaan teman.</p></div>
</div>
@endsection
